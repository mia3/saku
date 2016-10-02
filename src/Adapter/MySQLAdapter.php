<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:07
 */

namespace MIA3\Saku\Adapter;


class MySQLAdapter implements IndexAdapterInterface
{
    /**
     * @var \mysqli
     */
    protected $connection;

    protected $configuration;

    public function __construct($configuration)
    {
        $this->configuration = array_replace(array(
            'database' => NULL,
            'host' => 'localhost',
            'username' => NULL,
            'port' => NULL,
            'socket' => NULL,
            'table_prefix' => 'saku_',
            'mysql_engine' => 'MyISAM',
            'search_fields' => 'content'
        ), $configuration);

        $this->connection = new \mysqli(
            $this->configuration['host'],
            $this->configuration['username'],
            $this->configuration['password'],
            $this->configuration['database'],
            $this->configuration['port'],
            $this->configuration['socket']
        );

        $this->connection->query(sprintf('
            CREATE TABLE IF NOT EXISTS `%sobjects` (
                `id` int NOT NULL AUTO_INCREMENT,
                `objectId` varchar(4096) NOT NULL,
                `data` longblob NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=`%s`;
        ', $this->configuration['table_prefix'], $this->configuration['mysql_engine']));

        $this->connection->query(sprintf('
            CREATE TABLE IF NOT EXISTS `%scontents` (
                `id` int NOT NULL AUTO_INCREMENT,
                `object` int NOT NULL,
                `field` varchar(1024) NOT NULL,
                `content` longtext,
                PRIMARY KEY (`id`),
                FULLTEXT `content` (content)
            ) ENGINE=`%s`;
        ', $this->configuration['table_prefix'], $this->configuration['mysql_engine']));
    }

    public function addObject($object, $objectId) {
        $id = $this->getObject($objectId, serialize($object));
        $this->connection->query(sprintf('DELETE FROM %scontents WHERE object = "%s"', $this->configuration['table_prefix'], $id));

        foreach($object as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $childValue) {
                    if (is_array($childValue) || is_object($childValue)) {
                        continue;
                    }
                    $this->insertContent($id, $key, $childValue);
                }
            } else if (is_object($value)) {
                continue;
            } else {
                $this->insertContent($id, $key, $value);
            }
        }
    }

    protected function insertContent($id, $key, $value) {
        $query = $this->connection->prepare(sprintf('
                INSERT INTO %scontents
                    (object, field, content) 
                    VALUES(?, ?, ?)
                ',
            $this->configuration['table_prefix']
        ));
        $query->bind_param("iss", $id, $key, $value);
        $query->execute();
    }

    public function getObject($objectId, $data) {
        $query = sprintf('SELECT id FROM %sobjects WHERE objectId = "%s"', $this->configuration['table_prefix'], $objectId);
        $result = $this->connection->query($query);
        $row = $result->fetch_assoc();
        if (isset($row['id'])) {
            $query = $this->connection->prepare(sprintf('
                UPDATE %sobjects
                SET data = ?
                WHERE id = ?
                ',
                $this->configuration['table_prefix']
            ));
            $query->bind_param("si", $data, $row['id']);
            $query->execute();
            return intval($row['id']);
        }

        $query = $this->connection->prepare(sprintf('
                INSERT INTO %sobjects
                    (objectId, data) 
                    VALUES(?, ?)
                ',
            $this->configuration['table_prefix']
        ));
        $query->bind_param("ss", $objectId, $data);
        $query->execute();
        return $query->insert_id;
    }

    public function search($query, $options) {
        $contents = $this->getContents($query, $options);
        $results = array();
        foreach ($contents as $content) {
            $results[] = array_replace(
                $content,
                unserialize($content['data'])
            );
        }
        return array(
            'results' => $results,
            'total' => $this->getTotal($query, $options)
        );
    }

    public function getContents($query, $options) {
        $limit = isset($options['resultsPerPage']) ? $options['resultsPerPage'] : 10;
        $offset = isset($options['page']) ? (($options['page']) * $options['resultsPerPage']) : 0;
        $statement = $this->prepareStatement($query, $options, $limit, $offset);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function prepareStatement($query, $options, $limit, $offset) {
        $joins = array();
        $wheres = array();

        $contentsTable = $this->configuration['table_prefix'] . 'contents';
        $objectsTable = $this->configuration['table_prefix'] . 'objects';

        if (isset($options['facets'])) {
            foreach($options['facets'] as $facet => $value) {
                $joins[] = sprintf(
                    'JOIN %s as facet_%s 
                        ON rootContents.object = facet_%s.object' . chr(10),
                    $contentsTable,
                    $facet,
                    $facet
                );
                $wheres[] = sprintf(
                    'facet_%s.field = "%s" AND facet_%s.content = "%s"' . chr(10),
                    $facet,
                    $facet,
                    $facet,
                    $value
                );
            }
        }

        $searchFields = array();
        foreach(explode(',', $this->configuration['search_fields']) as $searchField) {
            $searchFields[] = '"' . $searchField . '"';
        }
        $wheres[] = sprintf(
            'MATCH(rootContents.content) AGAINST ("%s" IN NATURAL LANGUAGE MODE) AND rootContents.field IN (%s)',
            $this->connection->real_escape_string($query),
            implode(',', $searchFields)
        );
        $select = sprintf('*, MATCH(rootContents.content) AGAINST ("%s" IN NATURAL LANGUAGE MODE) AS score', $this->connection->real_escape_string($query));
        $joins[] = sprintf(
            'JOIN %s ON rootContents.object = %s.id',
            $objectsTable,
            $objectsTable
        );

        $query = sprintf(
            'SELECT %s
            FROM %s as  rootContents
            ' . implode(" \n", $joins) . '
            WHERE 
                ' . implode(" AND ", $wheres) . '
            ORDER BY score DESC
            LIMIT %s OFFSET %s
            ',
            $select,
            $contentsTable,
            $limit,
            $offset
        );
        $statement = $this->connection->prepare($query);
        return $statement;
    }

    public function getTotal($query, $options) {
        $statement = $this->prepareStatement($query, $options, PHP_INT_MAX, 0);
        $statement->execute();
        return $statement->get_result()->num_rows;
    }

    public function getFacet($configuration) {
        $contentsTable = $this->configuration['table_prefix'] . 'contents';
        $query = sprintf(
            'SELECT field, content as value, count(id) as count
            FROM %s 
            WHERE field = "%s"
            GROUP BY content
            ',
            $contentsTable,
            $configuration['field']
        );
        $statement = $this->connection->prepare($query);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}