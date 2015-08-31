<?php

namespace Pim\Bundle\CatalogBundle;

/**
 * Interface VersionProviderInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionProviderInterface
{
    /**
     * @return string
     */
    public function getEdition();

    /**
     * @return string
     */
    public function getMajor();

    /**
     * @return string
     */
    public function getMinor();

    /**
     * @return string
     */
    public function getPatch();

    /**
     * @return string
     */
    public function getStability();
}
