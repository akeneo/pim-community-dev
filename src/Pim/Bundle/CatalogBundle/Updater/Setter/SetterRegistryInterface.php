<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterRegistryInterface
{
    /**
     * Register a setter
     *
     * @param SetterInterface $setter
     */
    public function register(SetterInterface $setter);

    /**
     * Fetch the setter wich supports the field
     *
     * @param string $field
     *
     * @throws \LogicException
     *
     * @return SetterInterface
     */
    public function get($field);
}
