<?php

namespace PhpHelper\Tests;

use PhpHelper\Str;

/**
 * Class StrTest.
 */
class StrTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider charProvider
     *
     * @param int   $code
     * @param mixed $char
     */
    public function testOrd($code, $char)
    {
        $this->assertSame($code, Str::ord($char));
    }

    /**
     * @dataProvider charProvider
     *
     * @param int   $code
     * @param mixed $char
     */
    public function testChr($code, $char)
    {
        $this->assertSame($char, Str::chr($code));
    }

    /**
     * @return array
     */
    public function charProvider()
    {
        $chars = [];

        for ($i = 0; $i < 128; ++$i) {
            $chars[] = [$i, \chr($i)];
        }

        return array_merge($chars, [
            [0, "\0"],
            [0x9, "\t"],
            [0xA, "\n"],
            [0xD, "\r"],

            [0xA9, '©'],
            [0xC0, 'À'],
            [0xF7, '÷'],
            [0x190, 'Ɛ'],
            [0x3BC, 'μ'],
            [0x410, 'А'],
            [0x44B, 'ы'],
            [0x58D, '֍'],
            [0x1D6B, 'ᵫ'],
            [0x2211, '∑'],
            [0x22C5, '⋅'],
            [0x263A, '☺'],
            [0x2F65, '⽥'],
            [0x3576, '㕶'],
        ]);
    }

    /**
     * @dataProvider filterProvider
     *
     * @param string $expected
     * @param string $string
     * @param int    $options
     */
    public function testFilter($string, $expected, $options = Str::FILTER_TEXT)
    {
        $this->assertSame($expected, Str::filter($string, $options));
    }

    /**
     * @return array
     */
    public function filterProvider()
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
     *
     * @param string $string
     * @param string $slug
     * @param string $characters
     */
    public function testSlugify($string, $slug, $characters = '')
    {
        $this->assertSame($slug, Str::slugify($string, $characters));
    }

    /**
     * @return array
     */
    public function slugProvider()
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

    public function testGetRandomString()
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
     *
     * @param string $url
     * @param bool   $requiredScheme
     */
    public function testIsUrl($url, $requiredScheme = false)
    {
        $this->assertTrue(Str::isUrl($url, $requiredScheme), 'Failed: '.$url);
    }

    /**
     * @dataProvider invalidUrlProvider
     *
     * @param string $url
     * @param bool   $requiredScheme
     */
    public function testIsUrlInvalid($url, $requiredScheme = false)
    {
        $this->assertFalse(Str::isUrl($url, $requiredScheme));
    }

    /**
     * @return array
     */
    public function validUrlProvider()
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

    /**
     * @return array
     */
    public function invalidUrlProvider()
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
     *
     * @param string $email
     * @param bool   $result
     */
    public function testIsEmail($email, $result)
    {
        $this->assertSame($result, Str::isEmail($email), 'Failed: '.$email);
    }

    /**
     * @return array
     */
    public function emailProvider()
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

    public function testIsHash()
    {
        $this->assertTrue(Str::isHash(md5('test')));
        $this->assertTrue(Str::isHash('1234567890abcdef', 16));
        $this->assertFalse(Str::isHash('1234567890abcdef'));
        $this->assertTrue(Str::isHash('abc', 3));
        $this->assertTrue(Str::isHash(123, 3));
        $this->assertFalse(Str::isHash('xyz', 3));
        $this->assertTrue(Str::isHash(0xFF, 3));
        $this->assertFalse(Str::isHash(['array']));
    }

    /**
     * @dataProvider packProvider
     *
     * @param mixed $var
     */
    public function testPack($var)
    {
        $packed = Str::pack($var);
        $unpacked = Str::unpack($packed);
        $compressed = Str::pack($var, true);
        $uncompressed = Str::unpack($compressed, true);

        $this->assertEquals($var, $unpacked);
        $this->assertEquals($var, $uncompressed);
    }

    /**
     * @return array
     */
    public function packProvider()
    {
        $object = new \stdClass();
        $object->property = Str::getRandomString();

        return [
            [Str::getRandomString()],
            [[mt_rand(1, 999), mt_rand(1, 999), mt_rand(1, 999)]],
            [$object],
            [null],
            [mt_rand(1, 999)],
            [mt_rand(1, 9) / 10],
        ];
    }

    public function testPad()
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
     *
     * @param $string
     * @param $expected
     * @param $convention
     */
    public function testConvertCase($string, $expected, $convention)
    {
        $this->assertSame($expected, Str::convertCase($string, $convention));
    }

    /**
     * @return array
     */
    public function caseProvider()
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

    public function testGetShortClassName()
    {
        $strObject = new Str();
        $this->assertSame('Str', Str::getShortClassName($strObject));
        $this->assertSame('Str', Str::getShortClassName(Str::class));
    }
}
