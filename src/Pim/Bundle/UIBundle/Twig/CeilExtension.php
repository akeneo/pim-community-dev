<?php

namespace Pim\Bundle\UIBundle\Twig;

class CeilExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('ceil', array($this, 'ceil')),
        ];
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
}
