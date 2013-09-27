<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\ImageType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_image';
    protected $backendType = 'image';
    protected $formType = 'file';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        return new ImageType($this->backendType, $this->formType, $this->guesser);
    }
}
