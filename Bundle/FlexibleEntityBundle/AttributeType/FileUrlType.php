<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * File attribute type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FileUrlType extends UrlType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_fileurl';
    }
}
