<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\CatalogBundle\MassEditAction\EditCommonAttributes;
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
        $this->currencyManager     = $this->getCurrencyManagerMock(['EUR', 'USD']);

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
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionInterface', $this->action);
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
            ->will($this->returnValue([$sku, $name, $color, $description, $price]));

        $foo->expects($this->any())
            ->method('hasAttribute')
            ->will(
                $this->returnValueMap(
                    [
                        [$name,        true],
                        [$color,       true],
                        [$description, true],
                        [$price,       true],
                    ]
                )
            );

        $bar->expects($this->any())
            ->method('hasAttribute')
            ->will(
                $this->returnValueMap(
                    [
                        [$name,        true],
                        [$color,       false],
                        [$description, true],
                        [$price,       true],
                    ]
                )
            );

        $mobile = $this->getChannelMock('mobile');
        $web = $this->getChannelMock('web');

        $this->locale
            ->expects($this->any())
            ->method('getChannels')
            ->will($this->returnValue([$mobile, $web]));

        $this->productManager
            ->expects($this->any())
            ->method('createFlexibleValue')
            ->will($this->returnValue($this->getProductValueMock(null, null)));

        $this->action->setAttributesToDisplay(new ArrayCollection([$name, $description, $price]));
        $this->action->initialize([$foo, $bar]);

        $this->assertEquals([1 => $name, 3 => $description, 4 => $price], $this->action->getCommonAttributes());
        $this->assertEquals(
            ['name', 'description_mobile', 'description_web', 'price'],
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
            [
                $this->getProductPriceMock('EUR'),
                $this->getProductPriceMock('USD'),
            ]
        );

        $this->action->setValues(
            new ArrayCollection(
                [$newNameVal, $newDescriptionMobileVal, $newDescriptionWebVal, $newPriceVal]
            )
        );
        $this->action->setLocale($this->getLocaleMock('en_US'));

        $foo->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        ['name',         'en_US',  null,      $oldFooNameVal],
                        ['description',  null,     'mobile',  $oldFooDescriptionMobileVal],
                        ['description',  null,     'web',     $oldFooDescriptionWebVal],
                        ['price',        null,     null,      $oldFooPriceVal],
                    ]
                )
            );

        $bar->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        ['name',         'en_US',  null,      $oldBarNameVal],
                        ['description',  null,     'mobile',  $oldBarDescriptionMobileVal],
                        ['description',  null,     'web',     $oldBarDescriptionWebVal],
                        ['price',        null,     null,      $oldBarPriceVal],
                    ]
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
            ->with([$foo, $bar]);

        $this->productManager
            ->expects($this->once())
            ->method('saveAll')
            ->with([$foo, $bar], false);

        $this->action->perform([$foo, $bar]);
    }

    /**
     * Test related method
     */
    public function testFormType()
    {
        $this->assertEquals('pim_catalog_mass_edit_common_attributes', $this->action->getFormType());
    }

    /**
     * Test related method
     */
    public function testFormOptions()
    {
        $this->localeManager
            ->expects($this->any())
            ->method('getUserLocales')
            ->will($this->returnValue(['fr', 'en', 'pl']));

        $this->assertEquals(
            [
                'locales'          => ['fr', 'en', 'pl'],
                'commonAttributes' => [],
            ],
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
     * @param boolean $translatable
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Attribute
     */
    protected function getAttributeMock($code, $type = 'text', $scopable = false, $translatable = false)
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
            ->method('isTranslatable')
            ->will($this->returnValue($translatable));

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
    protected function getProductValueMock($attribute, $data, $scope = null, array $prices = [])
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
            ->setConstructorArgs([null, $currency])
            ->getMock();
    }
}
