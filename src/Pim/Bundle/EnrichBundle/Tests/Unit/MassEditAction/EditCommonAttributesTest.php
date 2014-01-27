<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\EnrichBundle\MassEditAction\EditCommonAttributes;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager       = $this->getObjectManagerMock();
        $this->attributeRepository = $this->getEntityRepositoryMock();
        $this->locale              = $this->getLocaleMock();

        $this->productManager      = $this->getProductManagerMock($this->objectManager, $this->attributeRepository);
        $this->localeManager       = $this->getLocaleManagerMock($this->locale);
        $this->currencyManager     = $this->getCurrencyManagerMock(array('EUR', 'USD'));

        $this->action              = new EditCommonAttributes(
            $this->productManager,
            $this->localeManager,
            $this->currencyManager
        );
    }

    /**
     * Test related method
     */
    public function testIsAMassEditAction()
    {
        $this->assertInstanceOf('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface', $this->action);
    }

    /**
     * Test related method
     */
    public function testInitialize()
    {
        $foo = $this->getProductMock();
        $bar = $this->getProductMock();

        $sku         = $this->getAttributeMock('sku', 'pim_catalog_identifier');
        $name        = $this->getAttributeMock('name', 'text', false, true);
        $color       = $this->getAttributeMock('color');
        $description = $this->getAttributeMock('description', 'text', true);
        $price       = $this->getAttributeMock('price', 'pim_catalog_price_collection');

        $this->attributeRepository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(array($sku, $name, $color, $description, $price)));

        $foo->expects($this->any())
            ->method('hasAttribute')
            ->will(
                $this->returnValueMap(
                    array(
                        array($name,        true),
                        array($color,       true),
                        array($description, true),
                        array($price,       true),
                    )
                )
            );

        $bar->expects($this->any())
            ->method('hasAttribute')
            ->will(
                $this->returnValueMap(
                    array(
                        array($name,        true),
                        array($color,       false),
                        array($description, true),
                        array($price,       true),
                    )
                )
            );

        $mobile = $this->getChannelMock('mobile');
        $web = $this->getChannelMock('web');

        $this->locale
            ->expects($this->any())
            ->method('getChannels')
            ->will($this->returnValue(array($mobile, $web)));

        $this->productManager
            ->expects($this->any())
            ->method('createFlexibleValue')
            ->will($this->returnValue($this->getProductValueMock(null, null)));

        $this->action->setAttributesToDisplay(new ArrayCollection(array($name, $description, $price)));
        $this->action->initialize(array($foo, $bar));

        $this->assertEquals(array(1 => $name, 3 => $description, 4 => $price), $this->action->getCommonAttributes());
        $this->assertEquals(
            array('name', 'description_mobile', 'description_web', 'price'),
            $this->action->getValues()->getKeys()
        );
    }

    /**
     * Test related method
     */
    public function testPerform()
    {
        $foo = $this->getProductMock();
        $bar = $this->getProductMock();

        $name        = $this->getAttributeMock('name', 'text', false, true);
        $description = $this->getAttributeMock('description', 'text', true);
        $price       = $this->getAttributeMock('price', 'pim_catalog_price_collection');

        $oldFooNameVal              = $this->getProductValueMock($name, null);
        $oldFooDescriptionMobileVal = $this->getProductValueMock($description, null, 'mobile');
        $oldFooDescriptionWebVal    = $this->getProductValueMock($description, null, 'web');
        $oldFooPriceVal             = $this->getProductValueMock($price, null);
        $oldFooPriceVal->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue(false));

        $oldBarNameVal              = $this->getProductValueMock($name, null);
        $oldBarDescriptionMobileVal = $this->getProductValueMock($description, null, 'mobile');
        $oldBarDescriptionWebVal    = $this->getProductValueMock($description, null, 'web');
        $oldBarPriceVal             = $this->getProductValueMock($price, null);
        $oldBarPriceVal->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue(false));

        $newNameVal              = $this->getProductValueMock($name, 'newName');
        $newDescriptionMobileVal = $this->getProductValueMock($description, 'newDescriptionMobile', 'mobile');
        $newDescriptionWebVal    = $this->getProductValueMock($description, 'newDescriptionWeb', 'web');
        $newPriceVal             = $this->getProductValueMock(
            $price,
            null,
            null,
            array(
                $this->getProductPriceMock('EUR'),
                $this->getProductPriceMock('USD'),
            )
        );

        $this->action->setValues(
            new ArrayCollection(
                array($newNameVal, $newDescriptionMobileVal, $newDescriptionWebVal, $newPriceVal)
            )
        );
        $this->action->setLocale($this->getLocaleMock('en_US'));

        $foo->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array('name',         'en_US',  null,      $oldFooNameVal),
                        array('description',  null,     'mobile',  $oldFooDescriptionMobileVal),
                        array('description',  null,     'web',     $oldFooDescriptionWebVal),
                        array('price',        null,     null,      $oldFooPriceVal),
                    )
                )
            );

        $bar->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array('name',         'en_US',  null,      $oldBarNameVal),
                        array('description',  null,     'mobile',  $oldBarDescriptionMobileVal),
                        array('description',  null,     'web',     $oldBarDescriptionWebVal),
                        array('price',        null,     null,      $oldBarPriceVal),
                    )
                )
            );

        $oldFooNameVal->expects($this->once())->method('setData')->with('newName');
        $oldFooDescriptionMobileVal->expects($this->once())->method('setData')->with('newDescriptionMobile');
        $oldFooDescriptionWebVal->expects($this->once())->method('setData')->with('newDescriptionWeb');

        $oldBarNameVal->expects($this->once())->method('setData')->with('newName');
        $oldBarDescriptionMobileVal->expects($this->once())->method('setData')->with('newDescriptionMobile');
        $oldBarDescriptionWebVal->expects($this->once())->method('setData')->with('newDescriptionWeb');

        $this->productManager
            ->expects($this->once())
            ->method('handleAllMedia')
            ->with(array($foo, $bar));

        $this->productManager
            ->expects($this->once())
            ->method('saveAll')
            ->with(array($foo, $bar), false);

        $this->action->perform(array($foo, $bar));
    }

    /**
     * Test related method
     */
    public function testFormType()
    {
        $this->assertEquals('pim_enrich_mass_edit_common_attributes', $this->action->getFormType());
    }

    /**
     * Test related method
     */
    public function testFormOptions()
    {
        $this->localeManager
            ->expects($this->any())
            ->method('getUserLocales')
            ->will($this->returnValue(array('fr', 'en', 'pl')));

        $this->assertEquals(
            array(
                'locales'          => array('fr', 'en', 'pl'),
                'commonAttributes' => array(),
            ),
            $this->action->getFormOptions()
        );
    }

    /**
     * @param mixed $objectManager
     * @param mixed $attributeRepository
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManagerMock($objectManager, $attributeRepository)
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManager));

        $manager->expects($this->any())
            ->method('getAttributeRepository')
            ->will($this->returnValue($attributeRepository));

        return $manager;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string  $code
     * @param string  $type
     * @param boolean $scopable
     * @param boolean $localizable
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Attribute
     */
    protected function getAttributeMock($code, $type = 'text', $scopable = false, $localizable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        $attribute->expects($this->any())
            ->method('isScopable')
            ->will($this->returnValue($scopable));

        $attribute->expects($this->any())
            ->method('isLocalizable')
            ->will($this->returnValue($localizable));

        $attribute->expects($this->any())
            ->method('getVirtualGroup')
            ->will($this->returnValue($this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeGroup')));

        return $attribute;
    }

    /**
     * @param mixed $locale
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock($locale)
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getLocaleByCode')
            ->will($this->returnValue($locale));

        return $manager;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function getLocaleMock($code = null)
    {
        $locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale');

        $locale->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $locale;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $channel;
    }

    /**
     * @param mixed $attribute
     * @param mixed $data
     * @param mixed $scope
     * @param array $prices
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValue
     */
    protected function getProductValueMock($attribute, $data, $scope = null, array $prices = array())
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($scope));

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $value->expects($this->any())
            ->method('getPrices')
            ->will($this->returnValue($prices));

        return $value;
    }

    /**
     * @param array $activeCodes
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CurrencyManager
     */
    protected function getCurrencyManagerMock(array $activeCodes)
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue($activeCodes));

        return $manager;
    }

    /**
     * @param mixed $currency
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductPrice
     */
    protected function getProductPriceMock($currency)
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductPrice')
            ->setConstructorArgs(array(null, $currency))
            ->getMock();
    }
}
