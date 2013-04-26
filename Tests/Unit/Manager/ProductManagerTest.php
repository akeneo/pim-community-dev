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
     * @test
     */
    public function itShouldDoNothingIfAttributeOfEntityToSaveIsNotTranslatable()
    {
        $target  = $this->getTargetedClass();
        $value   = $this->getValueMock(false);
        $product = $this->getProductMock(array($value));

        $product->expects($this->never())
                ->method('addValue');

        $value->expects($this->never())
              ->method('getLocale');

        $target->save($product);
    }

    /**
     * @test
     */
    public function itShouldAddMissingLocaleValuesForTranslatableAttributesBeforeInsertingAProduct()
    {
        $target  = $this->getTargetedClass();
        $product = $this->getProductMock(
            array(
                $this->getValueMock(true),
                $this->getValueMock(false),
            )
        );

        $product->expects($this->once())
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
        $product      = $this->getProductMock(array(
            $this->getValueMock(false, $media, 'baz')
        ), 'foobar');

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
        $value         = $this->getValueMock(false, $media, 'baz');
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
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
     */
    private function getTargetedClass($mediaManager = null, $objectManager = null)
    {
        return new ProductManager(
            'Product',
            array('entities_config' => array('Product' => null)),
            $objectManager ?: $this->getObjectManagerMock(),
            $this->getEventDispatcherInterfaceMock(),
            $mediaManager ?: $this->getMediaManagerMock()
        );
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
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\MediaManager
     */
    private function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ProductBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * Get a mock of ProductValue entity
     *
     * @param boolean $translatable
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    private function getValueMock($translatable = false, $media = null, $code = null)
    {
        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue', array(
            'getAttribute', 'getMedia', 'getLocale', 'setMedia'
        ));

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($this->getAttributeMock($translatable, $code)));

        $value->expects($this->any())
              ->method('getMedia')
              ->will($this->returnValue($media));

        $value->expects($this->any())
              ->method('getLocale')
              ->will($this->returnValue($translatable ? 'fr' : null));

        return $value;
    }

    /**
     * Get a mock of Attribute entity
     *
     * @param boolean $translatable
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Attribute
     */
    private function getAttributeMock($translatable = false, $code = null)
    {
        $attribute = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Attribute', array('getTranslatable', 'getCode'));

        $attribute->expects($this->any())
                  ->method('getTranslatable')
                  ->will($this->returnValue($translatable));

        $attribute->expects($this->any())
                  ->method('getCode')
                  ->will($this->returnValue($code));

        return $attribute;
    }

    /**
     * Get a mock of Product entity
     *
     * @param array $values
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    private function getProductMock(array $values, $sku = null)
    {
        $product = $this->getMock(
            'Pim\Bundle\ProductBundle\Entity\Product',
            array('getValues', 'getLanguages', 'addValue', 'getSku')
        );

        $product->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue(new ArrayCollection($values)));

        $product->expects($this->any())
                ->method('getLanguages')
                ->will(
                    $this->returnValue(
                        new ArrayCollection(array($this->getLanguageMock('fr'), $this->getLanguageMock('en')))
                    )
                );

        $product->expects($this->any())
                ->method('getSku')
                ->will($this->returnValue($sku));

        return $product;
    }

    /**
     * Get a mock of Language entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductLanguage
     */
    private function getLanguageMock($code)
    {
        $language = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductLanguage', array('getCode'));

        $language->expects($this->any())
                 ->method('getCode')
                 ->will($this->returnValue($code));

        return $language;
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
            ->getMock()
        ;

        $file->expects($this->any())
             ->method('getClientOriginalName')
             ->will($this->returnValue($filename));

        return $file;
    }
}
