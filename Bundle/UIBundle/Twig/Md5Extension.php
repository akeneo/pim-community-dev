<?php

namespace Oro\Bundle\UIBundle\Twig;

class Md5Extension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'md5' => new \Twig_Filter_Method($this, 'md5'),
        );
    }

    /**
     * PHP md5 wrapper
     *
     * @param string $string
     * @return int
     */
    public function md5($string)
    {
        return md5($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_md5';
    }
}
