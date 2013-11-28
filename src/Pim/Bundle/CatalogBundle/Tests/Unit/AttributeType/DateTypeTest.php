<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\DateType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_date';
    protected $backendType = AbstractAttributeType::BACKEND_TYPE_DATE;
    protected $formType = 'date';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new DateType($this->backendType, $this->formType, $this->guesser);
    }
}
