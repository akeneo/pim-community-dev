<?php

namespace Pim\Bundle\CatalogBundle\Twig;

/**
 * Twig filter to get entity FQCN
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectClassExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'class' => new \Twig_Filter_Method($this, 'getClass')
        );
    }

    /**
     * Get entity class name
     *
     * @param object $entity
     *
     * @return string
     */
    public function getClass($entity)
    {
        return get_class($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_object_class_extension';
    }
}
