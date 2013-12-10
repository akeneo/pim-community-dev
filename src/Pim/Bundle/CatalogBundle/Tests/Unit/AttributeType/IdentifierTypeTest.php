<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\CatalogBundle\AttributeType\IdentifierType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_identifier';
    protected $backendType = 'identifier';
    protected $formType = 'text';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new IdentifierType($this->backendType, $this->formType, $this->guesser);
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
            9,
            $attFormType
        );
    }
}
