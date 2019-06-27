<?php declare(strict_types=1);

namespace Casoa\Yii\Tests;

use Casoa\Yii\XmlParser;

class XmlParserTest extends TestCase
{
    private $_contentType = 'text/xml; charset=utf-8';
    private $_contentType2 = 'text/xml; charset=windows-1251';

    public function testParse(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<main>
    <name1>value1</name1>
    <name2>value2</name2>
</main>
XML;
        $arr = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];

        $parser = new XmlParser();

        $this->assertEquals($arr, $parser->parse($xml, $this->_contentType));
    }

    /**
     * @depends testParse
     */
    public function testParseCData(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<main>
    <name1><![CDATA[<tag>]]></name1>
    <name2><![CDATA[value2]]></name2>
</main>
XML;
        $arr = [
            'name1' => '<tag>',
            'name2' => 'value2',
        ];

        $parser = new XmlParser();

        $this->assertEquals($arr, $parser->parse($xml, $this->_contentType));
    }

    /**
     * @depends testParse
     */
    public function testParseEncoding(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<main>
    <enname>test</enname>
    <rusname>тест</rusname>
</main>
XML;
        $parser = new XmlParser();

        $parsed = $parser->parse($xml, $this->_contentType2);

        $this->assertEquals('test', $parsed['enname']);

        // UTF characters should be broken during parsing by 'windows-1251'
        $this->assertNotEquals('тест', $parsed['rusname']);
    }

    /**
     * @see https://github.com/yiisoft/yii2-httpclient/issues/102
     *
     * @depends testParse
     */
    public function testParseGroupTag(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<items>
    <item>
        <id>1</id>
        <name>item1</name>
    </item>
    <item>
        <id>2</id>
        <name>item2</name>
    </item>
</items>
XML;
        $arr = [
            'item' => [
                [
                    'id' => '1',
                    'name' => 'item1',
                ],
                [
                    'id' => '2',
                    'name' => 'item2',
                ],
            ],
        ];

        $parser = new XmlParser();

        $this->assertEquals($arr, $parser->parse($xml, $this->_contentType));
    }

    /**
     * @throws \ReflectionException
     */
    public function testConvertXmlToArray(): void
    {
        $string = <<<XML
<?xml version='1.0'?> 
<document>
 <title>test</title>
</document>
XML;
        $expectedArray = [
            'title' => 'test',
        ];

        $parser = new XmlParser();

        $array = $this->invoke($parser, 'convertXmlToArray', [$string]);

        $this->assertEquals($expectedArray, $array);
    }
}
