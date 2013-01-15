<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
* Test related class
*
* @author Nicolas Dupont <nicolas@akeneo.com>
* @copyright 2012 Akeneo SAS (http://www.akeneo.com)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*
*/
class ProductAttributeControllerTest extends WebTestCase
{
    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/flexibleproduct/productattribute/index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}