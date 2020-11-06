<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
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
     */
    public function getId(): int;

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;

    /**
     * Get groups
     */
    public function getGroups(): ArrayCollection;

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;

    /**
     * Returns the code
     *
     * @return string
     */
    public function __toString();
}
