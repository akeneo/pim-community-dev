<?php

namespace Pim\Bundle\GridBundle\Tests\Unit;

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

    protected function setUp()
    {
        $this->translator = new JavascriptRegExpTranslator('/root');
    }

    public function getTranslateData()
    {
        return array(
            "oro_search_results"=> array('#^/search/ajax$#s', '/^\/root\/search\/ajax$/'),
            "users" => array('#^/user(?:/(?P<_format>html|json))?$#s', '/^\/root\/user(\/(html|json))?$/'),
            "products" => array(
                '#^/enrich/product/(?:\.(?P<_format>html|json|csv))?$#s',
                '/^\/root\/enrich\/product\/(\.(html|json|csv))?$/'
            ),
        );
    }

    /**
     * @dataProvider getTranslateData
     * @param string $phpRegexp
     * @param string $javascriptRegexp
     */
    public function testTranslate($phpRegexp, $javascriptRegexp)
    {
        $this->assertEquals($javascriptRegexp, $this->translator->translate($phpRegexp));
    }

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
     * @expectedException Pim\Bundle\GridBundle\Exception\JavascriptRegexpTranslatorException
     * @dataProvider getTranslateExceptionData
     * @param string $phpRegexp
     */
    public function testTranslateException($phpRegexp) {
        $this->translator->translate($phpRegexp);
    }
}
