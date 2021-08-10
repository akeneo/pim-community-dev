<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Domain\Model\Selection\QuantifiedAssociations;

use Webmozart\Assert\Assert;

final class QuantifiedAssociationsLabelSelection implements QuantifiedAssociationsSelectionInterface
{
    public const TYPE = 'label';

    private string $entityType;
    private string $channel;
    private string $locale;
    private string $separator;

    public function __construct(
        string $entityType,
        string $channel,
        string $locale,
        string $separator
    ) {
        Assert::inArray($entityType, [
            self::ENTITY_TYPE_PRODUCTS,
            self::ENTITY_TYPE_PRODUCT_MODELS,
        ]);

        $this->entityType = $entityType;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->separator = $separator;
    }

    public function isProductsSelection(): bool
    {
        return self::ENTITY_TYPE_PRODUCTS === $this->entityType;
    }

    public function isProductModelsSelection(): bool
    {
        return self::ENTITY_TYPE_PRODUCT_MODELS === $this->entityType;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }
}
