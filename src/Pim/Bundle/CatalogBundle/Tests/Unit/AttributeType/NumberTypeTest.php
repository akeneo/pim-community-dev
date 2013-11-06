<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\NumberType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_number';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_DECIMAL;
    protected $formType = 'number';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new NumberType($this->backendType, $this->formType, $this->guesser);
    }
}
