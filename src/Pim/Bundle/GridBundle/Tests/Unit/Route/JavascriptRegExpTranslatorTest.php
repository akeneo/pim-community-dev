<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Route;

use Pim\Bundle\GridBundle\Route\JavascriptRegExpTranslator;

/**
 * Tests JavascriptRegExpTranslator
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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
            "oro_search_results"=> array('#^/search/ajax$#s', array(), '/^%prefix%\/search\/ajax$/'),
            "users" => array('#^/user(?:/(?P<_format>html|json))?$#s', array(), '/^%prefix%\/user(\/(html|json))?$/'),
            "products" => array(
                '#^/enrich/product/(?:\.(?P<_format>html|json|csv))?$#s',
                array(),
                '/^%prefix%\/enrich\/product\/(\.(html|json|csv))?$/'
            ),
            'custom' => array(
                '#^/enrich/(?P<customEntityName>[^/]++)/(?:\.(?P<_format>html|json|csv))?$#s',
                array('customEntityName' => 'value'),
                '/^%prefix%\/enrich\/value\/(\.(html|json|csv))?$/'
            ),
            'nested_replacement' => array(
                '#^/enrich/(?P<customEntityName>aabb(aa(bb)))/(?:\.(?P<_format>html|json|csv))?$#s',
                array('customEntityName' => 'value'),
                '/^%prefix%\/enrich\/value\/(\.(html|json|csv))?$/'
            )
        );
    }

    /**
     * Tests translate
     *
     * @param string $phpRegexp
     * @param array  $replacements
     * @param string $javascriptRegexp
     *
     * @dataProvider getTranslateData
     */
    public function testTranslate($phpRegexp, $replacements, $javascriptRegexp)
    {
        $this->assertEquals($javascriptRegexp, $this->translator->translate($phpRegexp, $replacements));
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
