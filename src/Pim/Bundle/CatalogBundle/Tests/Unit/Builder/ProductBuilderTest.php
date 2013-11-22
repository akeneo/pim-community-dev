<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Builder;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test relatede method
     */
    public function testAddMissingProductValues()
    {
        $builder = $this->getProductBuilder();
        $product = new Product();
        $this->assertEquals(count($product->getValues()), 0);

        $family = new Family();
        $attribute = new ProductAttribute();
        $attribute->setCode('one');
        $family->addAttribute($attribute);
        $product->setFamily($family);

        $builder->addMissingProductValues($product);
        $this->assertValues($product, array('one'));

        $attributeTwo = new ProductAttribute();
        $attributeTwo->setCode('two');
        $attributeTwo->setTranslatable(true);
        $family->addAttribute($attributeTwo);

        $builder->addMissingProductValues($product);
        $this->assertValues($product, array('one', 'twoen_US', 'twofr_FR'));

        $attributeThree = new ProductAttribute();
        $attributeThree->setCode('three');
        $attributeThree->setScopable(true);
        $family->addAttribute($attributeThree);

        $builder->addMissingProductValues($product);
        $this->assertValues($product, array('one', 'twoen_US', 'twofr_FR', 'threeecom', 'threeprint'));

        $attributeFour = new ProductAttribute();
        $attributeFour->setCode('four');
        $attributeFour->setTranslatable(true);
        $attributeFour->setScopable(true);
        $family->addAttribute($attributeFour);

        $builder->addMissingProductValues($product);
        $this->assertValues(
            $product,
            array(
                'one', 'twoen_US', 'twofr_FR', 'threeecom', 'threeprint',
                'fouren_USecom', 'fouren_USprint', 'fourfr_FRecom', 'fourfr_FRprint'
            )
        );
    }

    /**
     * Check product values
     *
     * @param Product $product   the product
     * @param array   $valueKeys the expected value keys (attCode + locale + scope)
     *
     * @return boolean
     */
    protected function assertValues($product, $valueKeys)
    {
        $nbValues = count($valueKeys);
        $values = $product->getValues();
        $this->assertEquals(count($values), $nbValues);

        foreach ($values as $value) {
            $key = $value->getAttribute()->getCode().$value->getLocale().$value->getScope();
            $this->assertTrue(in_array($key, $valueKeys));
        }
    }

    /**
     * @return ProductBuilder
     */
    protected function getProductBuilder()
    {
        $productClass = 'Pim\Bundle\CatalogBundle\Entity\Product';

        return new ProductBuilder($productClass, $this->getObjectManagerMock(), $this->getCurrencyManagerMock());
    }

    /**
     * @param array $activeCodes
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CurrencyManager
     */
    protected function getCurrencyManagerMock(array $activeCodes = array())
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
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        $mock = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $mock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->getClassMetadataMock()));
        $repos = array(
            array('PimCatalogBundle:Locale', $this->getLocaleRepositoryMock()),
            array('PimCatalogBundle:Channel', $this->getChannelRepositoryMock()),
        );
        $mock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($repos));

        return $mock;
    }

    /**
     * Get a mock of ClassMetadata
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function getClassMetadataMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getAssociationMappings')
            ->will(
                $this->returnValue(
                    array('values' => array('targetEntity' => 'Pim\Bundle\CatalogBundle\Entity\ProductValue'))
                )
            );

        return $mock;
    }

    /**
     * @return array
     */
    protected function getLocales()
    {
        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');

        return array($localeEn, $localeFr);
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getLocaleRepositoryMock()
    {
        $mock = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Repository\LocaleRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('getActivatedLocales')
            ->will($this->returnValue($this->getLocales()));

        return $mock;
    }

    /**
     * @return array
     */
    protected function getChannels()
    {
        $codes = array('ecom', 'print');
        $channels = array();

        foreach ($codes as $code) {
            $channel = new Channel();
            $channel->setCode($code);
            foreach ($this->getLocales() as $locale) {
                $channel->addLocale($locale);
            }
            $channels[] = $channel;
        }

        return $channels;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getChannelRepositoryMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($this->getChannels()));

        return $mock;
    }
}
