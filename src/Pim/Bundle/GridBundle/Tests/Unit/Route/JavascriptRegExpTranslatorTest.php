<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Route;

use Pim\Bundle\GridBundle\Route\JavascriptRegExpTranslator;

/**
 * Tests JavascriptRegExpTranslator
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS ("http" =>//www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JavascriptRegExpTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JavascriptRegExpTranslator
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->translator = new JavascriptRegExpTranslator();
    }

    /**
     * Data provider for testTranslate
     *
     * @return array
     */
    public function getTranslateData()
    {
        return array(
            "oro_search_results"=> array('#^/search/ajax$#s', '/^%prefix%\/search\/ajax$/'),
            "users" => array('#^/user(?:/(?P<_format>html|json))?$#s', '/^%prefix%\/user(\/(html|json))?$/'),
            "products" => array(
                '#^/enrich/product/(?:\.(?P<_format>html|json|csv))?$#s',
                '/^%prefix%\/enrich\/product\/(\.(html|json|csv))?$/'
            ),
        );
    }

    /**
     * Tests translate
     *
     * @param string $phpRegexp
     * @param string $javascriptRegexp
     *
     * @dataProvider getTranslateData
     */
    public function testTranslate($phpRegexp, $javascriptRegexp)
    {
        $this->assertEquals($javascriptRegexp, $this->translator->translate($phpRegexp));
    }

    /**
     * Data provider for testTranslateException
     *
     * @return array
     */
    public function getTranslateExceptionData()
    {
        return array(
            'history'          => array(
                '#^/audit/history(?:/(?P<entity>[a-zA-Z0-9_]+)(?:/(?P<id>\d+)(?:/(?P<_format>[^/]++))?)?)?$#s'
            ),
            'assert1'  => array('/(?=test)/'),
            'assert2'  => array('/(?!test)/'),
            'assert3'  => array('/(?<!test)/'),
            'assert4'  => array('/(?<=test)/')
        );
    }

    /**
     * Test unsupported regexps
     *
     * @param string $phpRegexp
     *
     * @expectedException Pim\Bundle\GridBundle\Exception\JavascriptRegexpTranslatorException
     * @dataProvider getTranslateExceptionData
     */
    public function testTranslateException($phpRegexp)
    {
        $this->translator->translate($phpRegexp);
    }
}
