<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product value for asset family
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionValue extends AbstractValue implements AssetCollectionValueInterface
{
    /**
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, ?array $assetCodes, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $assetCodes, $scopeCode, $localeCode);
    }

    /**
     * @return AssetCode[]
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $other): bool
    {
        if ($this->scopeCode !== $other->getScopeCode()
            || $this->localeCode !== $other->getLocaleCode()
        ) {
            return false;
        }

        if (count($this->getData()) !== count($other->getData())) {
            return false;
        }

        $iterator = new \MultipleIterator();
        $iterator->attachIterator(new \ArrayIterator($this->getData()));
        $iterator->attachIterator(new \ArrayIterator($other->getData()));

        foreach ($iterator as $iteratorValue) {
            if (!$iteratorValue[0]->equals($iteratorValue[1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? implode(array_map(function (AssetCode $assetCode) {
            return $assetCode->__toString();
        }, $this->data), ', ') : '';
    }
}
