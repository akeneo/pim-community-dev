<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Controller;

use Pim\Bundle\TestBundle\Tests\Controller\KernelAwareControllerTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class ProductAttributeControllerTest extends KernelAwareControllerTest
{

    /**
     * {@inheritdoc}
     */
    protected static $bundleName = 'flexibleproduct';

    /**
     * {@inheritdoc}
     */
    protected static $controller = 'productattribute';

    /**
     * Locales list used
     * @staticvar multitype:string
     */
    protected static $locales = array(
        'en', 'fr'
    );

    /**
     * Increment to generate values
     * @var integer
     */
    protected static $increment = 0;

    /**
     * test related action
     */
    public function testIndexAction()
    {
        foreach (self::$locales as $locale) {
            $this->client->request('GET', self::prepareUrl($locale, 'index'));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * test related action
     */
    public function testNewAction()
    {
        foreach (self::$locales as $locale) {
            $this->client->request('GET', self::prepareUrl($locale, 'new'));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * test related action
     */
    public function testCreateAction()
    {
        $insertCount = 0;
        // assert for each locales
        foreach (self::$locales as $locale) {
            // assert wrong with get method
            $this->client->request('GET', self::prepareUrl($locale, 'create'), self::prepareParams());
            $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

            // assert form calling new view
            $crawler = $this->client->request('GET', self::prepareUrl($locale, 'new'));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertCount(1, $crawler->filter('form'));

            $form = $crawler->selectButton('form-submit')->form();
            self::prepareParams($form);
            $crawler = $this->client->submit($form);
            $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
            $this->assertRedirectTo(self::prepareUrl($locale, ++$insertCount .'/edit'));
        }
    }

    /**
     * test related action
     */
    public function testEditAction()
    {
        // insert a product attribute in database
        $productAttribute = $this->insertProductAttribute();

        // assert for each locales
        foreach (self::$locales as $locale) {
            $crawler = $this->client->request('GET', self::prepareUrl($locale, $productAttribute->getId() .'/edit'));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertCount(1, $crawler->filter('form'));
        }
    }

    /**
     * test related action
     */
    public function testUpdateAction()
    {
        // insert a product attribute in database
        $productAttribute = $this->insertProductAttribute();
        $count = $this->countAttributes();

        // assert for each locales
        foreach (self::$locales as $locale) {
            // assert wrong with GET method
            $this->client->request('GET', self::prepareUrl($locale, $productAttribute->getId().'/update'), self::prepareParams());
            $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

            // assert form calling edit view
            $crawler = $this->client->request('GET', self::prepareUrl($locale, $productAttribute->getId().'/edit'));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertCount(1, $crawler->filter('form'));

            $form = $crawler->selectButton('form-submit')->form();
            // prepare form
            self::prepareParams($form);
            $form['pim_flexibleproduct_productattributetype[id]'] = $productAttribute->getId();
            $form['pim_flexibleproduct_productattributetype[attribute][id]'] = $productAttribute->getAttribute()->getId();

            $crawler = $this->client->submit($form);
            $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
            $this->assertRedirectTo(self::prepareUrl($locale, $productAttribute->getId().'/edit'));
            $this->assertEquals($count, $this->countAttributes());
        }
    }

    /**
     * test related action
     */
    public function testDeleteAction()
    {
        $startCount = $this->countAttributes();

        // assert for each locales
        foreach (self::$locales as $locale) {
            // insert a product attribute in database
            $productAttribute = $this->insertProductAttribute();
            $count = $this->countAttributes();
            $this->assertEquals($startCount+1, $count);

            // assert wrong with GET method
            $this->client->request('GET', self::prepareUrl($locale, $productAttribute->getId() .'/delete'));
            $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
            $this->assertRedirectTo(self::prepareUrl($locale, 'index'));

            // assert count in database
            $count = $this->countAttributes();
            $this->assertEquals($startCount, $count);
        }
    }

    /**
     * Prepare parameters for form submitting
     * @param Symfony\Component\DomCrawler\Form $form
     *
     * @return multitype
     *
     * @static
     */
    protected static function prepareParams($form = null)
    {
        self::$increment++;

        // generate random values
        $smart        = rand(0, 1) === 1 ? true : false;
        $required     = rand(0, 1) === 1 ? true : false;
        $unique       = rand(0, 1) === 1 ? true : false;
        $searchable   = rand(0, 1) === 1 ? true : false;
        $translatable = rand(0, 1) === 1 ? true : false;
        $scopable     = rand(0, 1);

        if ($form !== null) {
            // Set values to form
            $form['pim_flexibleproduct_productattributetype[name]'] = 'test-create-name-'. self::$increment;
            $form['pim_flexibleproduct_productattributetype[description]'] = 'Desc';
            $form['pim_flexibleproduct_productattributetype[smart]'] = $smart;

            $form['pim_flexibleproduct_productattributetype[attribute][code]'] = 'test-create-code'. self::$increment;
            $form['pim_flexibleproduct_productattributetype[attribute][backend_type]'] = 'varchar';
            $form['pim_flexibleproduct_productattributetype[attribute][backend_storage]'] = 'test-create-backend-storage-'. self::$increment;
            $form['pim_flexibleproduct_productattributetype[attribute][required]'] = $required;
            $form['pim_flexibleproduct_productattributetype[attribute][unique]'] = $unique;
            $form['pim_flexibleproduct_productattributetype[attribute][default_value]'] = 'test-default-value-'. self::$increment;
            $form['pim_flexibleproduct_productattributetype[attribute][searchable]'] = $searchable;
            $form['pim_flexibleproduct_productattributetype[attribute][translatable]'] = $translatable;
            $form['pim_flexibleproduct_productattributetype[attribute][scopable]'] = 0;
        } else {
            // return an array of values
            return array(
                'name' => 'test-create-name-'.self::$increment,
                'description' => 'test-create-description-'. self::$increment,
                'smart' => $smart,
                'attribute' => array(
                    'code' => 'test-create-code-'.self::$increment,
                    'backend-type' => 'varchar',
                    'backend-storage' => 'test-create-backend-storage-'. self::$increment,
                    'required' => $required,
                    'unique' => $unique,
                    'default-value' => 'test-default-value-'. self::$increment,
                    'searchable' => $searchable,
                    'translatable' => $translatable,
                    'scopable' => $scopable
                )
            );
        }
    }

    /**
     * Create an entity product attribute and persists it
     *
     * @return Pim\Bundle\FlexibleProduct\Entity\ProductAttribute
     */
    protected function insertProductAttribute()
    {
        $productAttribute = $this->createProductAttribute();
        $this->getProductManager()->getStorageManager()->persist($productAttribute);
        $this->getProductManager()->getStorageManager()->flush();

        return $productAttribute;
    }

    /**
     * Create a product attribute entity
     * @return Pim\Bundle\FlexibleProduct\Entity\ProductAttribute
     */
    protected function createProductAttribute()
    {
        // create product attribute object
        $productAttribute = $this->getProductManager()->createFlexibleAttribute();
        $attribute = $this->createAttribute();

        // set values to product attribute
        $productAttribute->setName('test-edit-name');
        $productAttribute->setDescription('test-edit-description');
        $productAttribute->setSmart(true);
        $productAttribute->setAttribute($attribute);

        return $productAttribute;
    }

    /**
     * Create an attribute entity
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute
     */
    protected function createAttribute()
    {
        // create attribute object
        $attribute = $this->getProductManager()->createAttribute();

        // set values to attribute
        $attribute->setCode('test-edit-code-'. microtime(true));
        $attribute->setBackendType('varchar');
        $attribute->setBackendStorage('test-edit-backend-storage');
        $attribute->setRequired(true);
        $attribute->setUnique(false);
        $attribute->setDefaultValue('test-default-value');
        $attribute->setSearchable(false);
        $attribute->setTranslatable(false);
        $attribute->setScopable(1);

        return $attribute;
    }

    /**
     * Count all product attributes
     * @return integer
     */
    protected function countAttributes()
    {
        $attributes = $this->getProductManager()->getAttributeRepository()->findAll();

        return count($attributes);
    }

    /**
     * Get the product manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim.flexible_product.product_manager');
    }

}