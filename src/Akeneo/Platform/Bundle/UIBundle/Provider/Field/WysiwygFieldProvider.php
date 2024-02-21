<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Field provider for wysiwyg
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WysiwygFieldProvider implements FieldProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getField($attribute)
    {
        return 'akeneo-wysiwyg-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            $element->isWysiwygEnabled();
    }
}
