<?php
namespace MIA3\Saku\Tests\Functional\Adapter;

/*
 * This file is part of the mia3/saku package.
 *
 * (c) Marc Neuhaus <marc@mia3.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MIA3\Saku\Adapter\MySQLAdapter;
use MIA3\Saku\Index;
use Stichoza\GoogleTranslate\TranslateClient;

/**
 * Class SearchWordHighlighterTest
 * @package MIA3\Saku\Tests\Unit
 */
class MySQLAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @dataProvider getNaughtyCharacterTestcases
     */
    public function testNaughtyCharacters($content, $expectations)
    {
        $index = new Index([
            'adapter' => MySQLAdapter::class,
            'database' => 'test_saku',
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'root',
        ]);
        $data = [
            'content' => $content,
        ];
        $id = 'textObject';
        $index->addObject($data, $id, 'someIndex');

        foreach ($expectations as $search => $hasResults) {
            $results = $index->search($search);
            $this->assertEquals(
                $hasResults,
                $results->count() > 0,
                'search for "' . $search . '" should' . ($hasResults ? '' : ' not') . ' yield results'
            );
        }
    }

    public function getNaughtyCharacterTestcases()
    {
        return [
            [ // Latin
                'ÜüÖöÄä',
                [
                    'üüööää' => true,
                    'ÜÜÖÖÄÄ' => true,
                    'üÜöÖäÄ' => true,
                ],
            ],
            [ // Latin
                'Insanity is doing the same thing, over and over again, but expecting different results.',
                [
                    'doing' => true,
                    'foo' => false,
                ],
            ],
            [ // Cyrillic
                'Безумие делает то же самое снова и снова, но ожидает разных результатов.',
                [
                    'самое' => true,
                    'foo' => false,
                ],
            ],
            [ // Greek
                'Η παραφροσύνη κάνει το ίδιο πράγμα ξανά και ξανά, αλλά αναμένει διαφορετικά αποτελέσματα.',
                [
                    'ξανά' => true,
                    'foo' => false,
                ],
            ],
            [ // Armenian
                'Անմեղսունակ է անում նույն բանը, կրկին ու կրկին, բայց սպասում տարբեր արդյունքներ:',
                [
                    'բայց' => true,
                    'foo' => false,
                ],
            ],
            [ // Georgian
                'აკეთებს იგივე, უსასრულოდ, მაგრამ ელოდება სხვადასხვა შედეგებს.',
                [
                    'ელოდება' => true,
                    'foo' => false,
                ],
            ],
            [ // Hangul
                '광기는 계속해서 똑같은 일을 반복하고 있지만 다른 결과를 기대합니다.',
                [
                    '결과를' => true,
                    'foo' => false,
                ],
            ],
            [ // Chinese
                '精神錯亂是一回事，但期待不同的結果。',
                [
                    '期待不' => true,
                    'foo' => false,
                ],
            ],
            [ // Kanji
                '狂気は何度も同じことを繰り返しているが、異なる結果を期待している。',
                [
                    'る結果' => true,
                    'foo' => false,
                ],
            ],
            [ // Arabic
                'الجنون يفعل الشيء نفسه، مرارا وتكرارا، ولكن نتوقع نتائج مختلفة.',
                [
                    'نفسه' => true,
                    'foo' => false,
                ],
            ],
            [ // Hebrew
                'הטירוף עושה את אותו הדבר, שוב ושוב, אבל מצפה לתוצאות שונות.',
                [
                    'אותו' => true,
                    'foo' => false,
                ],
            ],
            [ // Brahmic
                'पागलपन एक ही बात कर रहा है, बार बार, लेकिन विभिन्न परिणामों की उम्मीद है',
                [
                    'विभिन्न' => true,
                    'foo' => false,
                ],
            ],
            [ // Ge'ez
                'እብደት በላይ እና በላይ እንደገና, ተመሳሳይ ነገር በማድረግ, ነገር ግን የተለየ ውጤት መጠበቅ ነው.',
                [
                    'በማድረግ' => true,
                    'foo' => false,
                ],
            ],
        ];
    }
}
