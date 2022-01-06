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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslationInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAssetLabelsInterface;

class FindAssetLabels implements FindAssetLabelsInterface
{
    private FindAssetLabelTranslationInterface $findAssetLabelTranslation;

    public function __construct(FindAssetLabelTranslationInterface $findAssetLabelTranslation)
    {
        $this->findAssetLabelTranslation = $findAssetLabelTranslation;
    }

    public function byAssetFamilyCodeAndAssetCodes(
        string $assetFamilyCode,
        array $assetCodes,
        string $locale
    ): array {
        return $this->findAssetLabelTranslation->byFamilyCodeAndAssetCodes(
            $assetFamilyCode,
            $assetCodes,
            $locale
        );
    }
}
