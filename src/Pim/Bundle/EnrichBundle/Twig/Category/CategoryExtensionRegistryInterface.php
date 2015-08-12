<?php

namespace Pim\Bundle\EnrichBundle\Twig\Category;

use Pim\Component\Classification\Extension\CategoryExtensionInterface;

/**
 * Category extension registry
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryExtensionRegistryInterface
{
    /**
     * Register a category extension
     *
     * @param CategoryExtensionInterface $extension
     * @param string                     $type
     *
     * @return mixed
     */
    public function register(CategoryExtensionInterface $extension, $type);

    /**
     * Get category extension
     *
     * @param string $name
     *
     * @return CategoryExtensionInterface
     */
    public function get($name);
}
