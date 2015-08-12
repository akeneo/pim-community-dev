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
class CategoryExtensionRegistry implements CategoryExtensionRegistryInterface
{
    /** @var CategoryExtensionInterface[] */
    protected static $extensions = [];

    /**
     * {@inheritdoc}
     */
    public function register(CategoryExtensionInterface $extension, $type)
    {
        self::$extensions[$type] = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return self::$extensions[$name];
    }
}
