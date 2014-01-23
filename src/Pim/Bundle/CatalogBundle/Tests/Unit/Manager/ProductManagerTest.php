<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductBuilder;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function noValueAddedIfAttributeIsNotScopableNorTranslatable()
    {
        $target  = $this->getProductManager();
        $value   = $this->getValueMock();
        $product = $this->getProductMock([$value]);

        $product->expects($this->never())
            ->method('addValue');

        $target->save($product);
    }

    /**
     * @test
     */
    public function itShouldHandleMediasWhenValueHasOne()
    {
        $mediaManager = $this->getMediaManagerMock();
        $target       = $this->getProductManager($mediaManager);
        $media        = $this->getMediaMock('foo.jpg');
        $product      = $this->getProductMock(
            [$this->getValueMock(null, null, $media, 'baz')],
            'foobar'
        );

        $mediaManager->expects($this->once())
                     ->method('handle')
                     ->with($this->equalTo($media), $this->stringContains('foobar'));

        $target->handleMedia($product);
    }

    /**
     * Test related method
     */
    public function testCreateProductValue()
    {
        $value = $this->getProductManager()->createProductValue();
        $this->assertEquals(get_class($value), 'Pim\Bundle\CatalogBundle\Model\ProductValue');
    }

    /**
     * Test related method
     */
    public function testCreateProduct()
    {
        $product = $this->getProductManager()->createProduct();
        $this->assertEquals(get_class($product), 'Pim\Bundle\CatalogBundle\Model\Product');
    }

    /**
     * test related method
     */
    public function testSetLocale()
    {
        $pm = $this->getProductManager();
        $this->assertNull($pm->getLocale());
        $pm->setLocale('de_DE');
        $this->assertEquals($pm->getLocale(), 'de_DE');
    }

    /**
     * test related method
     */
    public function testSetScope()
    {
        $pm = $this->getProductManager();
        $this->assertNull($pm->getScope());
        $pm->setScope('mychan');
        $this->assertEquals($pm->getScope(), 'mychan');
    }

    /**
     * Create ProductManager
     *
     * @param MediaManager  $mediaManager
     * @param ObjectManager $objectManager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManager($mediaManager = null, $objectManager = null)
    {
        $flexibleRepository = $this->getEntityRepositoryMock();

        return new ProductManager(
            'Pim\Bundle\CatalogBundle\Model\Product',
            [
                'entities_config' => [
                    'Pim\Bundle\CatalogBundle\Model\Product' => [
                        'flexible_class' => 'Pim\Bundle\CatalogBundle\Model\Product',
                        'flexible_value_class' => 'Pim\Bundle\CatalogBundle\Model\ProductValue',
                        'attribute_class' => 'Pim\Bundle\CatalogBundle\Entity\Attribute',
                        'attribute_option_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
                        'attribute_option_value_class' => 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue',
                        'default_locale' => null,
                        'default_scope'  => null
                    ]
                ],
            ],
            $objectManager ?: $this->getObjectManagerMock($flexibleRepository),
            $objectManager ?: $this->getEntityManagerMock($flexibleRepository),
            $this->getEventDispatcherInterfaceMock(),
            $mediaManager ?: $this->getMediaManagerMock(),
            $this->getCompletenessManagerMock(),
            $this->getProductBuilderMock()
        );
    }

    /**
     * Get a mock of ObjectManager
     * @param mixed $repository
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock($repository)
    {
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $manager;
    }

    /**
     * Get a mock of EntityManager
     * @param mixed $repository
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock($repository)
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $manager;
    }

    /**
     * Get a mock of EventDispatcherInterface
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * Get a mock of AttributeTypeFactory
     *
     * @return Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory
     */
    protected function getAttributeTypeFactoryMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    protected function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a mock of ProductValue entity
     *
     * @param string $scope
     * @param string $locale
     * @param Media  $media
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValue
     */
    protected function getValueMock($scope = null, $locale = null, $media = null, $code = null)
    {
        $value = $this->getMock(
            'Pim\Bundle\CatalogBundle\Model\ProductValue',
            ['getAttribute', 'getMedia', 'setMedia']
        );

        $scopable = $scope ? true : false;
        $translatable = $locale ? true : false;
        $locale = $locale ?: 'en_US';

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($this->getAttributeMock($scopable, $translatable, $code)));

        if ($scopable) {
            $value->setScope($scope);
        }
        if ($translatable) {
            $value->setLocale($locale);
        }

        $value->expects($this->any())
              ->method('getMedia')
              ->will($this->returnValue($media));

        return $value;
    }

    /**
     * Get a mock of Attribute entity
     *
     * @param boolean $scopable
     * @param boolean $translatable
     * @param string  $code
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Entity\Attribute
     */
    protected function getAttributeMock($scopable = false, $translatable = false, $code = null)
    {
        $attribute = $this->getMock(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            ['isTranslatable', 'isScopable', 'getCode']
        );

        $attribute->expects($this->any())
                  ->method('isTranslatable')
                  ->will($this->returnValue($translatable));

        $attribute->expects($this->any())
                  ->method('isScopable')
                  ->will($this->returnValue($scopable));

        $attribute->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $attribute;
    }

    /**
     * Get a mock of Product entity
     *
     * @param array  $values
     * @param string $sku
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    protected function getProductMock(array $values, $sku = null)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue(new ArrayCollection($values)));

        $locales = [$this->getLocaleMock('fr_FR'), $this->getLocaleMock('en_US')];
        $product->expects($this->any())
                ->method('getActiveLocales')
                ->will(
                    $this->returnValue(new ArrayCollection($locales))
                );

        $product->expects($this->any())
                ->method('getIdentifier')
                ->will($this->returnValue($sku));

        return $product;
    }

    /**
     * Get a mock of Locale entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function getLocaleMock($code)
    {
        $locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale', ['getCode']);

        $locale->expects($this->any())
                 ->method('getCode')
                 ->will($this->returnValue($code));

        return $locale;
    }

    /**
     * Get a mock of Channel entity
     *
     * @param string $scope
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock($scope = null)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel', ['getCode']);

        $channel->expects($this->any())
                 ->method('getCode')
                 ->will($this->returnValue($scope));

        return $channel;
    }

    /**
     * Get a mock of Media entity
     *
     * @param string $filename
     *
     * @return \Pim\Bundle\CatalogBundle\Model\Media
     */
    protected function getMediaMock($filename = null)
    {
        $media = $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');

        $media->expects($this->any())
              ->method('getFile')
              ->will($this->returnValue($this->getFileMock($filename)));

        return $media;
    }

    /**
     * Get a mock of UploadedFile
     *
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected function getFileMock($filename)
    {
        $file = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setMethods(['getClientOriginalName'])
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->any())
             ->method('getClientOriginalName')
             ->will($this->returnValue($filename));

        return $file;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Builder\ProductBuilder
     */
    protected function getProductBuilderMock()
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Builder\ProductBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        return $manager;
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
     * @return \Pim\Bundle\CatalogBundle\Manager\CompletenessManager
     */
    protected function getCompletenessManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CompletenessManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
