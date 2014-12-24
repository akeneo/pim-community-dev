<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     * @param CopierInterface $copier
     */
    public function register(CopierInterface $copier);

    /**
     * Fetch the setter which supports the source and destination attributes
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @throws \LogicException
     *
     * @return CopierInterface
     */
    public function get(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
