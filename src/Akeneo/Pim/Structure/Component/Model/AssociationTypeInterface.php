<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

/**
 * Association type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationTypeInterface extends
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface,
    TimestampableInterface
{
    /**
     * Get id
     */
    public function getId(): int;

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id): \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;

    /**
     * Get code
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;

    /**
     * @return bool
     */
    public function isTwoWay(): bool;

    /**
     * @param bool $isTwoWay
     */
    public function setIsTwoWay(bool $isTwoWay): void;

    /**
     * Returns the label of the association type
     *
     * @return string
     */
    public function __toString();

    public function isQuantified(): bool;

    public function setIsQuantified(bool $quantified): void;
}
