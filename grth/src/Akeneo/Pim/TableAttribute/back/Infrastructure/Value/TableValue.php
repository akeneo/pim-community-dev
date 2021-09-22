<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;

class TableValue extends AbstractValue
{
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value->getData() instanceof Table) {
            return false;
        }

        return $this->getScopeCode() === $value->getScopeCode() &&
            $this->getLocaleCode() === $value->getLocaleCode() &&
            $this->data->normalize() == $value->getData()->normalize();
        // the non-strict comparison is not an error, we want to compare the table values regardless of the cells order
    }

    public function getData(): Table
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return \json_encode($this->data->normalize());
    }
}
