<?php
namespace Pim\Bundle\ProductBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\TextAreaType as OroTextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Text area attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TextAreaType extends OroTextAreaType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormAlias(FlexibleValueInterface $value)
    {
        if ($value->getAttribute()->getWysiwygEnabled()) {
            return 'pim_wysiwyg';
        }

        return parent::prepareValueFormAlias($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_textarea';
    }
}
