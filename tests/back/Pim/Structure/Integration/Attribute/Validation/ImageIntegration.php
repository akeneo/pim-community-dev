<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageIntegration extends AbstractAttributeTestCase
{
    public function testImageIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_image');
    }

    public function testImageIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_image');
    }

    public function testImageHasValidAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_image',
                'type'               => 'pim_catalog_image',
                'group'              => 'attributeGroupA',
                'allowed_extensions' => ['png', 'invalid'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('"invalid" is not a supported image extension. Valid extensions are: gif, jfif, jif, jpeg, jpg, pdf, png, psd, tif, tiff', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testImageShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_image');
    }

    public function testImageShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_image');
    }

    public function testImageShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_image');
    }

    public function testImageShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_image');
    }

    public function testImageShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_image');
    }

    public function testImageShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_image');
    }

    public function testImageShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_image');
    }

    public function testImageShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_image');
    }

    public function testImageShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_image');
    }

    public function testImageShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_image');
    }

    public function testImageShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_image');
    }

    public function testImageShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_image');
    }

    public function testImageShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_image');
    }

    public function testImageShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_image');
    }
}
