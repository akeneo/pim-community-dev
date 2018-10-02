<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Doctrine\Common\Util\ClassUtils;

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
        return [
            new \Twig_SimpleFilter('class', array($this, 'getClass')),
        ];
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
        return ClassUtils::getClass($entity);
    }
}
