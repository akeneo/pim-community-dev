<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Manager\ProductManager;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->markTestSkipped('Due BAP Upgrade, changes into flexible entity manager');
        parent::setUp();
    }

    /**
     * @test
     */
    public function noValueAddedIfAttributeIsNotScopableOrTranslatable()
    {
        $target  = $this->getTargetedClass();
        $value   = $this->getValueMock();
        $product = $this->getProductMock(array($value));

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
        $target       = $this->getTargetedClass($mediaManager);
        $media        = $this->getMediaMock('foo.jpg');
        $product      = $this->getProductMock(
            array($this->getValueMock(null, null, $media, 'baz')),
            'foobar'
        );

        $mediaManager->expects($this->once())
                     ->method('handle')
                     ->with($this->equalTo($media), $this->stringContains('foobar'));

        $target->save($product);
    }

    /**
     * @test
     */
    public function itShouldRemoveMediaEntityIfMediaIsMeantToBeRemoved()
    {
        $mediaManager  = $this->getMediaManagerMock();
        $objectManager = $this->getObjectManagerMock();
        $target        = $this->getTargetedClass($mediaManager, $objectManager);
        $media         = $this->getMediaMock('foo.jpg');
        $value         = $this->getValueMock(null, null, $media, 'baz');
        $product       = $this->getProductMock(array($value), 'foobar');

        $media->expects($this->any())
              ->method('isRemoved')
              ->will($this->returnValue(true));

        $objectManager->expects($this->once())
                      ->method('remove')
                      ->with($this->equalTo($media));

        $value->expects($this->once())
              ->method('setMedia')
              ->with($this->equalTo(null));

        $target->save($product);
    }

    /**
     * Create ProductManager
     *
     * @param MediaManager  $mediaManager
     * @param ObjectManager $objectManager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
     */
    private function getTargetedClass($mediaManager = null, $objectManager = null)
    {
        $productManager = $this->getMock(
            'Pim\Bundle\ProductBundle\Manager\ProductManager',
            array('getChannels', 'createFlexibleValue'),
            array(
                'Product',
                array('entities_config' => array('Product' => null)),
                $objectManager ?: $this->getObjectManagerMock(),
                $this->getEventDispatcherInterfaceMock(),
                $this->getAttributeTypeFactoryMock(),
                $mediaManager ?: $this->getMediaManagerMock()
            )
        );

        $productManager->expects($this->any())
            ->method('getChannels')
            ->will(
                $this->returnValue(
                    array(
                        $this->getChannelMock('ecommerce'),
                        $this->getChannelMock('mobile')
                    )
                )
            );

        $productManager->expects($this->any())
            ->method('createFlexibleValue')
            ->will($this->returnValue($this->getValueMock()));

        return $productManager;
    }

    /**
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * Get a mock of EventDispatcherInterface
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * Get a mock of AttributeTypeFactory
     *
     * @return Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory
     */
    private function getAttributeTypeFactoryMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\MediaManager
     */
    private function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\MediaManager')
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
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    private function getValueMock($scope = null, $locale = null, $media = null, $code = null)
    {
        $value = $this->getMock(
            'Pim\Bundle\ProductBundle\Entity\ProductValue',
            array('getAttribute', 'getMedia', 'setMedia')
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
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Attribute
     */
    private function getAttributeMock($scopable = false, $translatable = false, $code = null)
    {
        $attribute = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute',
            array('getTranslatable', 'getScopable', 'getCode')
        );

        $attribute->expects($this->any())
                  ->method('getTranslatable')
                  ->will($this->returnValue($translatable));

        $attribute->expects($this->any())
                  ->method('getScopable')
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
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    private function getProductMock(array $values, $sku = null)
    {
        $product = $this->getMock(
            'Pim\Bundle\ProductBundle\Entity\Product',
            array('getValues', 'getLocales', 'addValue', 'getSku')
        );

        $product->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue(new ArrayCollection($values)));

        $locales = array($this->getLocaleMock('fr_FR'), $this->getLocaleMock('en_US'));
        $product->expects($this->any())
                ->method('getActiveLocales')
                ->will(
                    $this->returnValue(new ArrayCollection($locales))
                );

        $product->expects($this->any())
                ->method('getSku')
                ->will($this->returnValue($sku));

        return $product;
    }

    /**
     * Get a mock of Locale entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductLocale
     */
    private function getLocaleMock($code)
    {
        $locale = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductLocale', array('getCode'));

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
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    private function getChannelMock($scope = null)
    {
        $channel = $this->getMock('Pim\Bundle\ConfigBundle\Entity\Channel', array('getCode'));

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
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    private function getMediaMock($filename = null)
    {
        $media = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Media', array('getFile', 'isRemoved'));

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
    private function getFileMock($filename)
    {
        $file = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setMethods(array('getClientOriginalName'))
            ->disableOriginalConstructor()
            ->getMock();

        $file->expects($this->any())
             ->method('getClientOriginalName')
             ->will($this->returnValue($filename));

        return $file;
    }
}
