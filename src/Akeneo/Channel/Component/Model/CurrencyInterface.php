<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;

/**
 * Currency interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CurrencyInterface extends ReferableInterface
{
    public function getId(): int;

    public function getCode(): string;

    /**
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Channel\Component\Model\CurrencyInterface;

    public function isActivated(): bool;

    /**
     * @param bool $activated
     */
    public function setActivated(bool $activated): \Akeneo\Channel\Component\Model\CurrencyInterface;

    /**
     * Toggle activation
     */
    public function toggleActivation(): \Akeneo\Channel\Component\Model\CurrencyInterface;
}
