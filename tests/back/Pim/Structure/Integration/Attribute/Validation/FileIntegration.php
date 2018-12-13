<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileIntegration extends AbstractAttributeTestCase
{
    public function testFileIsNotRequired()
    {
        $this->assertNotRequired('pim_catalog_file');
    }

    public function testFileIsNotUnique()
    {
        $this->assertNotUnique('pim_catalog_file');
    }

    public function testFileHasValidAllowedExtensions()
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'               => 'new_file',
                'type'               => 'pim_catalog_file',
                'group'              => 'attributeGroupA',
                'allowed_extensions' => ['doc', 'invalid'],
            ]
        );

        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('"invalid" is not a supported file extension. Valid extensions are: csv, doc, docx, mp3, pdf, ppt, pptx, rtf, svg, txt, wav', $violations->get(0)->getMessage());
        $this->assertSame('allowedExtensions', $violations->get(0)->getPropertyPath());
    }

    public function testFileShouldNotHaveAMetricFamily()
    {
        $this->assertDoesNotHaveAMetricFamily('pim_catalog_file');
    }

    public function testFileShouldNotHaveADefaultMetricUnit()
    {
        $this->assertDoesNotHaveADefaultMetricUnit('pim_catalog_file');
    }

    public function testFileShouldNotHaveAReferenceDataName()
    {
        $this->assertDoesNotHaveAReferenceDataName('pim_catalog_file');
    }

    public function testFileShouldNotHaveAutoOptionSorting()
    {
        $this->assertDoesNotHaveAutoOptionSorting('pim_catalog_file');
    }

    public function testFileShouldNotHaveMaxCharacters()
    {
        $this->assertDoesNotHaveMaxCharacters('pim_catalog_file');
    }

    public function testFileShouldNotHaveAValidationRule()
    {
        $this->assertDoesNotHaveAValidationRule('pim_catalog_file');
    }

    public function testFileShouldNotHaveAValidationRegexp()
    {
        $this->assertDoesNotHaveAValidationRegexp('pim_catalog_file');
    }

    public function testFileShouldNotHaveWysiwygEnabled()
    {
        $this->assertDoesNotHaveWysiwygEnabled('pim_catalog_file');
    }

    public function testFileShouldNotHaveANumberMin()
    {
        $this->assertDoesNotHaveANumberMin('pim_catalog_file');
    }

    public function testFileShouldNotHaveANumberMax()
    {
        $this->assertDoesNotHaveANumberMax('pim_catalog_file');
    }

    public function testFileShouldNotHaveDecimalsAllowed()
    {
        $this->assertDoesNotHaveDecimalsAllowed('pim_catalog_file');
    }

    public function testFileShouldNotHaveNegativeAllowed()
    {
        $this->assertDoesNotHaveNegativeAllowed('pim_catalog_file');
    }

    public function testFileShouldNotHaveADateMin()
    {
        $this->assertDoesNotHaveADateMin('pim_catalog_file');
    }

    public function testFileShouldNotHaveADateMax()
    {
        $this->assertDoesNotHaveADateMax('pim_catalog_file');
    }
}
