<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Interface for options product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionsValueInterface extends ValueInterface
{
    /**
     * Return options codes
     */
    public function getData(): ?array;

    public function hasCode(string $code): bool;
    public function getOptionCodes(): array;
}
