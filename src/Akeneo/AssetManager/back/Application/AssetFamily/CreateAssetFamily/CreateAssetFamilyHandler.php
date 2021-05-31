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

namespace Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAssetFamilyHandler
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function __invoke(CreateAssetFamilyCommand $createAssetFamilyCommand): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($createAssetFamilyCommand->identifier),
            $createAssetFamilyCommand->labels,
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules($createAssetFamilyCommand->productLinkRules),
        )->withNamingConvention(
            NamingConvention::createFromNormalized($createAssetFamilyCommand->namingConvention)
        );

        $this->assetFamilyRepository->create($assetFamily);
    }
}
