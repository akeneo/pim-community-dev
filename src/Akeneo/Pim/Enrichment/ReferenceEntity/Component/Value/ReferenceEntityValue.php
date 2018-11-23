<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * Product value for a reference entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityValue extends AbstractValue implements ReferenceEntityValueInterface
{
    /**
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?RecordCode
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (null === $this->getData() || null === $value->getData()) {
            $areEqual = ($this->getData() === $value->getData());
        } else {
            $areEqual = $this->getData()->equals($value->getData());
        }

        return $areEqual
            && $this->getScopeCode() === $value->getScopeCode()
            && $this->getLocaleCode() === $value->getLocaleCode();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (null !== $this->data) ? (string) $this->data : '';
    }
}
