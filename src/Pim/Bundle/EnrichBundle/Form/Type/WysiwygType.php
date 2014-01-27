<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Extends Textarea type with WYSIWYG features
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WysiwygType extends TextareaType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_wysiwyg';
    }
}
