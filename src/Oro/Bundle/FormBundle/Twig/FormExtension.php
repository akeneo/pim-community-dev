<?php

namespace Oro\Bundle\FormBundle\Twig;

use Oro\Bundle\FormBundle\Form\Twig\DataBlocks;

/**
 * Class FormExtension
 *
 * TODO: only used for system/conf
 */
class FormExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'form_data_blocks',
                [new DataBlocks, 'render'],
                ['needs_context' => true, 'needs_environment' => true]
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_form';
    }
}
