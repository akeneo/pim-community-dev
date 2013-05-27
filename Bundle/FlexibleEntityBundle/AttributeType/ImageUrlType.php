<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * Image attribute type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ImageUrlType extends FileUrlType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_imageurl';
    }
}
