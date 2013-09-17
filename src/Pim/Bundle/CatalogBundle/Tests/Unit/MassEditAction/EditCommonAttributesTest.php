<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\CatalogBundle\MassEditAction\EditCommonAttributes;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->objectManager       = $this->getObjectManagerMock();
        $this->attributeRepository = $this->getEntityRepositoryMock();
        $this->locale              = $this->getLocaleMock();

        $this->productManager      = $this->getFlexibleManagerMock($this->objectManager, $this->attributeRepository);
        $this->localeManager       = $this->getLocaleManagerMock($this->locale);
        $this->currencyManager     = $this->getCurrencyManagerMock(array('EUR', 'USD'));

        $this->action              = new EditCommonAttributes(
            $this->productManager,
            $this->localeManager,
            $this->currencyManager
        );
    }

    public function testIsAMassEditAction()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\MassEditAction\MassEditAction', $this->action);
    }

    public function testInitialize()
    {
        $foo = $this->getProductMock();
        $bar = $this->getProductMock();

        $sku         = $this->getProductAttributeMock('sku', 'pim_catalog_identifier');
        $name        = $this->getProductAttributeMock('name', 'text', false, true);
        $color       = $this->getProductAttributeMock('color');
        $description = $this->getProductAttributeMock('description', 'text', true);
        $price       = $this->getProductAttributeMock('price', 'pim_catalog_price_collection');

        $this->attributeRepository
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(array($sku, $name, $color, $description, $price)));

        $foo->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array('name',         null,  null,  true),
                        array('color',        null,  null,  true),
                        array('description',  null,  null,  true),
                        array('price',        null,  null,  true),
                    )
                )
            );

        $bar->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array('name',         null,  null,  true),
                        array('color',        null,  null,  false),
                        array('description',  null,  null,  true),
                        array('price',        null,  null,  true),
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

    public function testPerform()
    {
        $foo = $this->getProductMock();
        $bar = $this->getProductMock();

        $name        = $this->getProductAttributeMock('name');
        $description = $this->getProductAttributeMock('description', 'text', true);
        $price       = $this->getProductAttributeMock('price', 'pim_catalog_price_collection');

        $oldFooNameVal              = $this->getProductValueMock($name, null);
        $oldFooDescriptionMobileVal = $this->getProductValueMock($description, null, 'mobile');
        $oldFooDescriptionWebVal    = $this->getProductValueMock($description, null, 'web');
        $oldFooPriceVal             = $this->getProductValueMock($price, null);

        $oldBarNameVal              = $this->getProductValueMock($name, null);
        $oldBarDescriptionMobileVal = $this->getProductValueMock($description, null, 'mobile');
        $oldBarDescriptionWebVal    = $this->getProductValueMock($description, null, 'web');
        $oldBarPriceVal             = $this->getProductValueMock($price, null);

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
                        array('description',  'en_US',  'mobile',  $oldFooDescriptionMobileVal),
                        array('description',  'en_US',  'web',     $oldFooDescriptionWebVal),
                        array('price',        'en_US',  null,      $oldFooPriceVal),
                    )
                )
            );

        $bar->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    array(
                        array('name',         'en_US',  null,      $oldBarNameVal),
                        array('description',  'en_US',  'mobile',  $oldBarDescriptionMobileVal),
                        array('description',  'en_US',  'web',     $oldBarDescriptionWebVal),
                        array('price',        'en_US',  null,      $oldBarPriceVal),
                    )
                )
            );

        $oldFooNameVal->expects($this->once())->method('setData')->with('newName');
        $oldFooDescriptionMobileVal->expects($this->once())->method('setData')->with('newDescriptionMobile');
        $oldFooDescriptionWebVal->expects($this->once())->method('setData')->with('newDescriptionWeb');

        $oldBarNameVal->expects($this->once())->method('setData')->with('newName');
        $oldBarDescriptionMobileVal->expects($this->once())->method('setData')->with('newDescriptionMobile');
        $oldBarDescriptionWebVal->expects($this->once())->method('setData')->with('newDescriptionWeb');

        $this->action->perform(array($foo, $bar));
    }

    public function testFormType()
    {
        $this->assertEquals('pim_catalog_mass_edit_common_attributes', $this->action->getFormType());
    }

    public function testFormOptions()
    {
        $this->localeManager
            ->expects($this->any())
            ->method('getActiveLocales')
            ->will($this->returnValue(array('fr', 'en', 'pl')));

        $this->assertEquals(
            array(
                'locales'          => array('fr', 'en', 'pl'),
                'commonAttributes' => array(),
            ),
            $this->action->getFormOptions()
        );
    }

    protected function getFlexibleManagerMock($objectManager, $attributeRepository)
    {
        $manager = $this
            ->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getStorageManager')
            ->will($this->returnValue($objectManager));

        $manager->expects($this->any())
            ->method('getAttributeRepository')
            ->will($this->returnValue($attributeRepository));

        return $manager;
    }

    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }

    protected function getEntityRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getProductAttributeMock($code, $type = 'text', $scopable = false, $translatable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }

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

    protected function getLocaleMock($code = null)
    {
        $locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale');

        $locale->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $locale;
    }

    protected function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $channel;
    }

    protected function getProductValueMock($attribute, $data, $scope = null, array $prices = array())
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductValue');

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

    protected function getProductPriceMock($currency)
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\ProductPrice')
            ->setConstructorArgs(array(null, $currency))
            ->getMock();
    }
}
