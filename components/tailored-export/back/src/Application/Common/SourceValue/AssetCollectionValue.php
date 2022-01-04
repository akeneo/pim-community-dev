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

namespace Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Webmozart\Assert\Assert;

class AssetCollectionValue implements SourceValueInterface
{
    /** @var string[] */
    private array $assetCodes;
    private string $entityIdentifier;
    private ?string $channel;
    private ?string $locale;

    public function __construct(
        array $assetCodes,
        string $entityIdentifier,
        ?string $channel,
        ?string $locale
    ) {
        Assert::allString($assetCodes);

        $this->assetCodes = $assetCodes;
        $this->entityIdentifier = $entityIdentifier;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    public function getAssetCodes(): array
    {
        return $this->assetCodes;
    }

    public function getEntityIdentifier(): string
    {
        return $this->entityIdentifier;
    }

    public function getChannelReference(): ?string
    {
        return $this->channel;
    }

    public function getLocaleReference(): ?string
    {
        return $this->locale;
    }
}
