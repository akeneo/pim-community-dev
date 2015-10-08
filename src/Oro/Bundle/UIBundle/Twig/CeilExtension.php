<?php

namespace Oro\Bundle\UIBundle\Twig;

class CeilExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_Filter_Method($this, 'ceil'),
        );
    }

    /**
     * PHP ceil wrapper
     *
     * @param float $number
     * @return int
     */
    public function ceil($number)
    {
        return ceil($number);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_ceil';
    }
}
