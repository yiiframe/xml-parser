<?php declare(strict_types=1);

namespace Casoa\Yii;

use Yii;
use DOMDocument;
use yii\base\BaseObject;
use yii\web\RequestParserInterface;

/**
 * XmlParser parses HTTP message content as XML.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @see https://github.com/yiisoft/yii2-httpclient/blob/master/src/XmlParser.php
 * @since 2.0
 */
class XmlParser extends BaseObject implements RequestParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($rawBody, $contentType): array
    {
        if (preg_match('/charset=(.*)/i', $contentType, $matches)) {
            $encoding = $matches[1];
        } else {
            $encoding = Yii::$app->charset;
        }

        $dom = new DOMDocument('1.0', $encoding);
        $dom->loadXML($rawBody, LIBXML_NOCDATA);

        return $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    protected function convertXmlToArray($xml): array
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        $result = (array)$xml;
        foreach ($result as $key => $value) {
            if (!is_scalar($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
}
