<?php
namespace MIA3\Saku\Tests\Unit;

/*
 * This file is part of the mia3/saku package.
 *
 * (c) Marc Neuhaus <marc@mia3.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MIA3\Saku\SearchWordHighlighter;

/**
 * Class SearchWordHighlighterTest
 * @package MIA3\Saku\Tests\Unit
 */
class SearchWordHighlighterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @dataProvider getSomeHighlightingTestValues
     * @param $string
     * @param $words
     * @param $wrap
     * @param $crop
     * @param $prefix
     * @param $suffix
     * @param $wordsBeforeMatch
     * @param $expectation
     * @internal param string $comparison
     * @internal param bool $expected
     */
    public function testHighlighting($string, $words, $wrap, $crop, $prefix, $suffix, $wordsBeforeMatch, $expectation)
    {
        $highlighter = new SearchWordHighlighter($string);
        $highlighter->setWrap($wrap);
        $highlighter->setCrop($crop);
        $highlighter->setPrefix($prefix);
        $highlighter->setSuffix($suffix);
        $highlighter->setWordsBeforeMatch($wordsBeforeMatch);

        $this->assertEquals($highlighter->highlight($words), $expectation);
    }

    /**
     * @return array
     */
    public function getSomeHighlightingTestValues()
    {
        return array(
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "lorem amet",
                'wrap' => '<b>|</b>',
                'crop' => null,
                'prefix' => null,
                'suffix' => null,
                'wordsBeforeMatch' => null,
                'expecation' => '<b>Lorem</b> ipsum dolor sit <b>amet</b>, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti",
                'wrap' => '<b>|</b>',
                'crop' => null,
                'prefix' => null,
                'suffix' => null,
                'wordsBeforeMatch' => null,
                'expecation' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi <b>deleniti</b> dolore esseeveniet excepturi ipsum iusto.',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti",
                'wrap' => '<b>|</b>',
                'crop' => null,
                'prefix' => null,
                'suffix' => null,
                'wordsBeforeMatch' => 3,
                'expecation' => 'elit. Accusamus animi <b>deleniti</b> dolore esseeveniet excepturi ipsum iusto.',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti ipsum",
                'wrap' => '<b>|</b>',
                'crop' => null,
                'prefix' => null,
                'suffix' => null,
                'wordsBeforeMatch' => 3,
                'expecation' => 'elit. Accusamus animi <b>deleniti</b> dolore esseeveniet excepturi <b>ipsum</b> iusto.',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti ipsum",
                'wrap' => '<b>|</b>',
                'crop' => 6,
                'prefix' => null,
                'suffix' => null,
                'wordsBeforeMatch' => 3,
                'expecation' => 'elit. Accusamus animi <b>deleniti</b> dolore esseeveniet',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti ipsum",
                'wrap' => '<b>|</b>',
                'crop' => 6,
                'prefix' => '...',
                'suffix' => null,
                'wordsBeforeMatch' => 3,
                'expecation' => '...elit. Accusamus animi <b>deleniti</b> dolore esseeveniet',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus animi deleniti dolore esseeveniet excepturi ipsum iusto.',
                'words' => "deleniti ipsum",
                'wrap' => '<b>|</b>',
                'crop' => 6,
                'prefix' => '...',
                'suffix' => '...',
                'wordsBeforeMatch' => 3,
                'expecation' => '...elit. Accusamus animi <b>deleniti</b> dolore esseeveniet...',
            ),
            array(
                'string' => 'Lorem ipsum dolor sit amet, consectetur adipisicing 
                
                elit. Accusamus animi deleniti dolore esseeveniet        excepturi ipsum iusto.',
                'words' => "deleniti ipsum",
                'wrap' => '<b>|</b>',
                'crop' => 6,
                'prefix' => '...',
                'suffix' => '...',
                'wordsBeforeMatch' => 3,
                'expecation' => '...elit. Accusamus animi <b>deleniti</b> dolore esseeveniet...',
            ),
            array(
                'string' => 'Lorem ipsum TÜV sit amet, consectetur adipisicing',
                'words' => "tüv",
                'wrap' => '<b>|</b>',
                'crop' => 6,
                'prefix' => '...',
                'suffix' => '...',
                'wordsBeforeMatch' => 3,
                'expecation' => 'Lorem ipsum <b>TÜV</b> sit amet, consectetur...',
            ),
        );
    }
}
