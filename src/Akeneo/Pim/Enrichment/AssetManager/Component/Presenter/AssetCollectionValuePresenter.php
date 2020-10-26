<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Presenter;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetCollectionValuePresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AssetCollectionType::ASSET_COLLECTION === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($assetCollection)
    {
        if (!is_iterable($assetCollection)) {
            return [];
        }

        return array_map(function (AssetCode $assetCode) {
            return $assetCode->normalize();
        }, $assetCollection);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}
