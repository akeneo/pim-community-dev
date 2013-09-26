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
            'objectMd5' => new \Twig_Filter_Method($this, 'objectMd5'),
        );
    }

    /**
     * PHP md5 wrapper
     *
     * @param string $string
     * @return string
     */
    public function md5($string)
    {
        return md5($string);
    }

    /**
     *  Returns md5 of serialized object for objects and md5 for string values
     *
     * @param mixed $object
     * @return string
     */
    public function objectMd5($object)
    {
        $hash = '';
        if (is_object($object)) {
            $hash = md5(serialize($object));
        } elseif (is_string($object)) {
            $hash = md5($object);
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_md5';
    }
}
