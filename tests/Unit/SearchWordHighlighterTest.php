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
            array(
                'string' => '         Resultater/løsninger igennem samarbejdeI Gothia håndterer vi både private og erhvervsdebitorer med at afvikle deres gæld på den bedste og hurtigste måde, så både vores kunder og debitorer er tilfredse med vores løsninger.Ved at have en god dialog med debitorerne, kan vi vurdere debitorernes vilje til at betale og deres betalingsevne. Sammen med vores scoringsmodel, finder vi den bedste løsning for alle og herved sikre jer et højt inddrivelsesniveau.I vores grundige samarbejde med debitorerne, finder vi den rette betalingsløsning, så vi værner om jeres brand og øger kundetilfredsheden.Gothia Debt Collection er en del af Arvato Financial Solutions, og vores inkassoløsning er inkluderet i vores portefølje af løsninger, der dækker hele kundens livscyklus: fra risikostyring og fakturering til factoring og inkasso.  Læs mere om factoring og vores fakturering og rykkerservice her.  Vil du vide mere om vores inkassoløsninger, så klik her            Vil I tale med vores ekspert?         Susan Nymann  Send en mail til Susan Nymann                      Vi har hjulpet mange mennesker med at få overblik over deres gæld.        Sådan hjalp vi Telenor      Sådan hjalp vi Telenor  Telenor Norge er e succesfuldt ”business case” på at finde balance mellem at inddrive gæld og fastholde værdifulde kunder.  Derfor sammenligner de Arvatos resultater med andre inkassobureauer, hvor Arvato konstant ligger højest.  Vil du vide mere om Telenors og Arvatos samarbejde for bedre inkassoinddrivelse?                                      Vores inkassoløsningertil jeres virksomhed                                     Vores inkassoløsningertil jeres virksomhed                     ',
                'words' => "debt",
                'wrap' => '<b>|</b>',
                'crop' => 3,
                'prefix' => '...',
                'suffix' => '...',
                'wordsBeforeMatch' => 1,
                'expecation' => '...kundetilfredsheden.Gothia <b>Debt</b> Collection...',
            )
        );
    }
}
