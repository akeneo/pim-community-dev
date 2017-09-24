<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Group type interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupTypeInterface extends TranslatableInterface, ReferableInterface
{
    /**
     * Get the id
     *
     * @return int
     */
    public function getId();

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return GroupTypeInterface
     */
    public function setCode($code);

    /**
     * Is variant
     *
     * @deprecated will be remove with 2.0
     *
     * @return bool
     */
    public function isVariant();

    /**
     * Set variant
     *
     * @deprecated will be remove with 2.0
     *
     * @param bool $variant
     *
     * @return GroupTypeInterface
     */
    public function setVariant($variant);

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups();

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return GroupTypeInterface
     */
    public function setLabel($label);

    /**
     * Returns the code
     *
     * @return string
     */
    public function __toString();
}
