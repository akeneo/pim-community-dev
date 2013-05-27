<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Text attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TextType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_text';
    }
}
