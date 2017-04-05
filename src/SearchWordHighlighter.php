<?php
namespace MIA3\Saku;

/*
 * This file is part of the mia3/saku package.
 *
 * (c) Marc Neuhaus <marc@mia3.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class WrapWordsViewHelper
 * @package MIA3\Mia3Search\ViewHelpers
 */
class SearchWordHighlighter
{

    protected $string;
    protected $words = array();
    protected $wrap = '<strong>|</strong>';
    protected $crop = null;
    protected $suffix = '&hellip;';
    protected $prefix = '&hellip;';
    protected $wordsBeforeMatch = 10;

    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @param string $wrap
     */
    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
    }

    /**
     * @param null $crop
     */
    public function setCrop($crop)
    {
        $this->crop = $crop;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param int $wordsBeforeMatch
     */
    public function setWordsBeforeMatch($wordsBeforeMatch)
    {
        $this->wordsBeforeMatch = $wordsBeforeMatch;
    }

    /**
     *
     * @param string $string
     * @param mixed $words
     * @param string $wrap
     * @param integer $crop
     * @param string $suffix
     * @param string $prefix
     * @param integer $wordsBeforeMatch
     * @return string
     */
    public function highlight($words)
    {
        if (!is_array($words)) {
            $words = preg_split('/[ ,\.\?\-]/s', trim($words, ' ,.?+-'));
        }

        $words = array_filter($words, function($word){
            return strlen($word) > 1;
        });

        $string = $this->string;

        if ($this->wordsBeforeMatch > 0) {
            $string = $this->cutBeforeMatch($string, $words);
        }

        if ($this->crop !== null) {
            $string = $this->cropWords($string);
        }

        $replacement = str_replace('|', '$0', $this->wrap);
        foreach ($words as $word) {
            // do a case-insensitive replace to find all case-sensitive matches
            $string = preg_replace('/' . preg_quote($word, '/') . '/i', $replacement, $string);
        }

        return trim($string);
    }

    public function cutBeforeMatch($content, $words)
    {
        $contentWords = preg_split('/[\s]/s', trim($content));
        $word = reset($words);
        foreach ($contentWords as $key => $contentWord) {
            similar_text(strtolower(trim($contentWord)), strtolower(trim($word)), $match);
            if ($match > 80) {
                // if the there are less words before the first search word, we can
                // break here, because cutting before the first word makes no sense
                if ($this->wordsBeforeMatch > $key) {
                    break;
                }

                // look for the position of the first search word
                $startPosition = strpos($content, $contentWord);

                for ($i = 1; $i <= $this->wordsBeforeMatch; $i++) {
                    if (isset($contentWords[$key - $i])) {
                        $startPosition -= strlen($contentWords[$key - $i]) + 1;
                    }
                }
                $content = $this->prefix . trim(substr($content, $startPosition));
                break;
            }
        }

        return $content;
    }

    public function cropWords($text)
    {
        $text = strip_tags($text);

        $words = preg_split("/[\n\r\t ]+/", $text, $this->crop + 1, PREG_SPLIT_NO_EMPTY);
        $separator = ' ';

        if (count($words) > $this->crop) {
            array_pop($words);
            $text = implode($separator, $words);
            $text = $text . $this->suffix;
        } else {
            $text = implode($separator, $words);
        }

        return $text;
    }
}
