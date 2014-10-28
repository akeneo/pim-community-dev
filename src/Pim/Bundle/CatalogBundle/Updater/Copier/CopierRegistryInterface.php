<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

/**
 * Registry of copiers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CopierRegistryInterface
{
    /**
     * Register a copier
     *
     * @param CopierInterface $setter
     */
    public function register(CopierInterface $setter);

    /**
     * Fetch the copier which supports the copy of a field to another
     *
     * @param string $sourceField
     * @param string $destField
     *
     * @throws \LogicException
     *
     * @return CopierInterface
     */
    public function get($sourceField, $destField);
}
