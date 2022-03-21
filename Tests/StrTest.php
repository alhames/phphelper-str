<?php

namespace PhpHelper\Tests;

use PhpHelper\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testFilter(string $string, string $expected, int $options = Str::FILTER_TEXT): void
    {
        $this->assertSame($expected, Str::filter($string, $options));
    }

    public function filterProvider(): array
    {
        return [
            ['Hello world!', 'Hello world!'],
            ['Hi! ©', 'Hi! '],
            ['Hi! ©', 'Hi! ', Str::FILTER_TEXT],
            ['Hi! ©', 'Hi! &#169;', Str::FILTER_HTML],
            ['Hi! ©', 'Hi! [%00A9]', Str::FILTER_CODE],
            ['Hi! ©', 'Hi!', Str::FILTER_TEXT | Str::FILTER_SPACE],
            ['Hi! ©', 'Hi! &#169;', Str::FILTER_HTML | Str::FILTER_SPACE],

            [" Hi! ©\nHello! ", " Hi! \nHello! ", Str::FILTER_TEXT],
            [" Hi! ©\nHello! ", " Hi! &#169;\nHello! ", Str::FILTER_HTML],
            [" Hi! ©\nHello! ", 'Hi! Hello!', Str::FILTER_TEXT | Str::FILTER_SPACE],
            [" Hi! ©\nHello! ", 'Hi! &#169; Hello!', Str::FILTER_HTML | Str::FILTER_SPACE],

            ['Почти «Сталкер»:  впечатления — обзор', 'Почти Сталкер:  впечатления  обзор', Str::FILTER_TEXT],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти &#171;Сталкер&#187;:  впечатления &#8212; обзор', Str::FILTER_HTML],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти Сталкер: впечатления обзор', Str::FILTER_TEXT | Str::FILTER_SPACE],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти &#171;Сталкер&#187;: впечатления &#8212; обзор', Str::FILTER_HTML | Str::FILTER_SPACE],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти "Сталкер":  впечатления - обзор', Str::FILTER_TEXT | Str::FILTER_PUNCTUATION],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти "Сталкер":  впечатления - обзор', Str::FILTER_HTML | Str::FILTER_PUNCTUATION],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти "Сталкер": впечатления - обзор', Str::FILTER_TEXT | Str::FILTER_PUNCTUATION | Str::FILTER_SPACE],
            ['Почти «Сталкер»:  впечатления — обзор', 'Почти "Сталкер": впечатления - обзор', Str::FILTER_HTML | Str::FILTER_PUNCTUATION | Str::FILTER_SPACE],
        ];
    }

    /**
     * @dataProvider slugProvider
     */
    public function testSlugify(string $string, string $slug, string $characters = ''): void
    {
        $this->assertSame($slug, Str::slugify($string, $characters));
    }

    public function slugProvider(): array
    {
        return [
            ['абв', 'abv'],
            ['длинное название чего-либо', 'dlinnoe_nazvanie_chego_libo'],
            ['fileName.txt', 'filename_txt'],
            ['fileName.txt', 'filename.txt', '.'],
            ['Заглавная буква в Начале слова и Предложения', 'zaglavnaya_bukva_v_nachale_slova_i_predlozheniya'],
            ['counter-strike', 'counter_strike'],
            ['counter-strike', 'counter-strike', '-'],
            ['snake_case', 'snake_case'],
            ['@#df$%щф&^жуpor', 'df_schf_zhupor'],
        ];
    }

    public function testGetRandomString(): void
    {
        $randomStrings = [];
        for ($i = 0; $i < 10000; ++$i) {
            $randomStrings[] = Str::getRandomString();
        }

        $this->assertCount(10000, array_unique($randomStrings));
        $this->assertSame(111, \strlen(Str::getRandomString(111)));
    }

    /**
     * @dataProvider validUrlProvider
     */
    public function testIsUrl(string $url, bool $requiredScheme = false): void
    {
        $this->assertTrue(Str::isUrl($url, $requiredScheme), 'Failed: '.$url);
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function testIsUrlInvalid(string $url, bool $requiredScheme = false): void
    {
        $this->assertFalse(Str::isUrl($url, $requiredScheme));
    }

    public function validUrlProvider(): array
    {
        return [
            ['google.com'],
            ['www.google.com'],
            ['http://google.com'],
            ['http://www.google.com'],
            ['https://google.com'],
            ['https://google.com/'],
            ['//google.com'],
            ['http://google.com/?test=abc'],
            ['http://google.com/path/?test=abc'],
            ['http://google.com/path/?test=abc', true],
            ['https://google.com/path/?test=abc#fdf', true],
            ['i.ua'],
            ['abcdef.gallery'],
            ['https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D0%B3%D1%83%D0%BB%D1%8F%D1%80%D0%BD%D1%8B%D0%B5_%D0%B2%D1%8B%D1%80%D0%B0%D0%B6%D0%B5%D0%BD%D0%B8%D1%8F'],
            ['https://ru.wikipedia.org/wiki/Регулярные_выражения'],
            ['my-site.com:8080'],
            ['my-site.com:8080/index.html'],
            ['http://my-site.com/video.mp4'],
        ];
    }

    public function invalidUrlProvider(): array
    {
        return [
            ['google.com', true],
            ['//google.com', true],
            ['google'],
            ['$google.com'],
            ['http://.google.com'],
            ['http://google..com'],
            ['http://google.com.'],
            ['http://-google.com'],
            ['http://google-.com'],
            ['ftp://google.com'],
            ['ftp://google.com/'],
        ];
    }

    /**
     * @see https://habrahabr.ru/post/318698/
     *
     * @dataProvider emailProvider
     */
    public function testIsEmail(string $email, bool $result): void
    {
        $this->assertSame($result, Str::isEmail($email), 'Failed: '.$email);
    }

    public function emailProvider(): array
    {
        return [
            // valid emails
            ['AbC@domain.com', true],
            ['user@domain.com', true],
            ['abc@gmail.com', true],
            ['abc+1@gmail.com', true],
            ['ab.c@gmail.com', true],
            ['a-b.c@gmail.com', true],
            ['a.b.c@gmail.com', true],
            ['a@i.ua', true],
            ['a@i.gallery', true],

            // invalid emails
            ['domain.com', false],
            ['abc@', false],
            ['abc@gmail', false],
            ['a@bc@gmail.com', false],
            ['abc@-gmail.com', false],
            ['abc@gmail-.com', false],
            ['abc@.gmail.com', false],
            ['abc@gmail.com.', false],
            ['abc@gmail..com', false],
            ['.abc@gmail.com', false],
            ['abc.@gmail.com', false],
            ['ab..c@gmail.com', false],
            ['"abc"@gmail.com', false],
            ['-f"attacker\" -oQ/tmp/ -X/var/www/cache/phpcode.php  some"@email.com', false],
        ];
    }

    public function testPad(): void
    {
        $this->assertSame('абв   ', Str::pad('абв', 6));
        $this->assertSame('   абв', Str::pad('абв', 6, ' ', STR_PAD_LEFT));
        $this->assertSame(' абв  ', Str::pad('абв', 6, ' ', STR_PAD_BOTH));
        $this->assertSame('абв---', Str::pad('абв', 6, '-'));
        $this->assertSame('00001', Str::pad(1, 5, 0, STR_PAD_LEFT));
        $this->assertSame('абвгд', Str::pad('абвгд', 3));
    }

    /**
     * @dataProvider caseProvider
     */
    public function testConvertCase(string $string, string $expected, int $convention): void
    {
        $this->assertSame($expected, Str::convertCase($string, $convention));
    }

    public function caseProvider(): array
    {
        $strings = [
            //   source           camelCase       CamelCase       snake_case        SNAKE_CASE        kebab-case        Kebab-Case
            ['simple',        'simple',       'Simple',       'simple',         'SIMPLE',         'simple',         'Simple'],
            ['two words',     'twoWords',     'TwoWords',     'two_words',      'TWO_WORDS',      'two-words',      'Two-Words'],
            ['some number 1', 'someNumber1',  'SomeNumber1',  'some_number_1',  'SOME_NUMBER_1',  'some-number-1',  'Some-Number-1'],
            ['1 first digit', '1FirstDigit',  '1FirstDigit',  '1_first_digit',  '1_FIRST_DIGIT',  '1-first-digit',  '1-First-Digit'],
            ['me 1 in mid',   'me1InMid',     'Me1InMid',     'me_1_in_mid',    'ME_1_IN_MID',    'me-1-in-mid',    'Me-1-In-Mid'],
            ['HTML',          'html',         'Html',         'html',           'HTML',           'html',           'Html'],
            ['image.jpg',     'imageJpg',     'ImageJpg',     'image_jpg',      'IMAGE_JPG',      'image-jpg',      'Image-Jpg'],
            ['simpleXML',     'simpleXml',    'SimpleXml',    'simple_xml',     'SIMPLE_XML',     'simple-xml',     'Simple-Xml'],
            ['PDFLoad',       'pdfLoad',      'PdfLoad',      'pdf_load',       'PDF_LOAD',       'pdf-load',       'Pdf-Load'],
            ['loadHTMLFile',  'loadHtmlFile', 'LoadHtmlFile', 'load_html_file', 'LOAD_HTML_FILE', 'load-html-file', 'Load-Html-File'],
            ['PHP_INT_MAX',   'phpIntMax',    'PhpIntMax',    'php_int_max',    'PHP_INT_MAX',    'php-int-max',    'Php-Int-Max'],
            ['ICar',          'iCar',         'ICar',         'i_car',          'I_CAR',          'i-car',          'I-Car'],
            ['Disk:C',        'diskC',        'DiskC',        'disk_c',         'DISK_C',         'disk-c',         'Disk-C'],
            ['one_TwoThree',  'oneTwoThree',  'OneTwoThree',  'one_two_three',  'ONE_TWO_THREE',  'one-two-three',  'One-Two-Three'],
            [' _some--MIX-',  'someMix',      'SomeMix',      'some_mix',       'SOME_MIX',       'some-mix',       'Some-Mix'],
            ['UP123low',      'up123Low',     'Up123Low',     'up_123_low',     'UP_123_LOW',     'up-123-low',     'Up-123-Low'],
        ];

        $conventions = [
            null,
            Str::CASE_CAMEL_LOWER,
            Str::CASE_CAMEL_UPPER,
            Str::CASE_SNAKE_LOWER,
            Str::CASE_SNAKE_UPPER,
            Str::CASE_KEBAB_LOWER,
            Str::CASE_KEBAB_UPPER,
        ];

        $data = [];
        $total = \count($conventions);

        for ($i = 1; $i < $total; ++$i) {
            foreach ($strings as $string) {
                $data[] = [$string[0], $string[$i], $conventions[$i]];
                for ($j = 1; $j < $total; ++$j) {
                    if ($j !== $i) {
                        $data[] = [$string[$j], $string[$i], $conventions[$i]];
                    }
                }
            }
        }

        return $data;
    }

    public function testGetShortClassName(): void
    {
        $strObject = new Str();
        $this->assertSame('Str', Str::getShortClassName($strObject));
        $this->assertSame('Str', Str::getShortClassName(Str::class));
    }
}
