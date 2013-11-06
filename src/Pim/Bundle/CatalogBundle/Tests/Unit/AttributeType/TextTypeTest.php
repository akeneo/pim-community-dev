<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\TextAreaType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_textarea';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_TEXT;
    protected $formType = 'textarea';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new TextAreaType($this->backendType, $this->formType, $this->guesser);
    }
}
