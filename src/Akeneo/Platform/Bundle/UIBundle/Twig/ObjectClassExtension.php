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
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('class', fn($entity) => $this->getClass($entity)),
        ];
    }

    /**
     * Get entity class name
     *
     * @param object $entity
     */
    public function getClass(object $entity): string
    {
        return ClassUtils::getClass($entity);
    }
}
