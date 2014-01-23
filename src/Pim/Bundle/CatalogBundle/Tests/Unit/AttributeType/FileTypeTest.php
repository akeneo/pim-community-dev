<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\CatalogBundle\AttributeType\FileType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_file';
    protected $backendType = 'file';
    protected $formType = 'file';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new FileType($this->backendType, $this->formType, $this->guesser);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeMock($backendType, $defaultValue, array $attributeOptions = [])
    {
        $attribute = parent::getAttributeMock($backendType, $defaultValue, $attributeOptions);

        $attribute
            ->expects($this->any())
            ->method('getAllowedExtensions')
            ->will($this->returnValue(['pdf', 'odt', 'doc', 'xls']));

        return $attribute;
    }

    /**
     * Test related method
     */
    public function testBuildAttributeFormTypes()
    {
        $attFormType = $this->target->buildAttributeFormTypes(
            $this->getFormFactoryMock(),
            $this->getAttributeMock(null, null)
        );

        $this->assertCount(
            6,
            $attFormType
        );
    }
}
