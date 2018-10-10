<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Reference data interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReferenceDataInterface
{
    /**
     * Get the ID of the reference data
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get the code (unique) of the reference data
     *
     * @return string
     */
    public function getCode();

    /**
     * Set the code (unique) of the reference data
     *
     * @param string $code
     *
     * @return ReferenceDataInterface
     */
    public function setCode($code);

    /**
     * Get the order in which the reference data will be displayed
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get the property that will be used as label to be displayed in the PIM.
     * If no property is returned, the [code] of the reference data will be
     * displayed in the PIM.
     *
     * @return string|null
     */
    public static function getLabelProperty();

    /**
     * To string
     *
     * @return string
     */
    public function __toString();
}
