<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByCodesInterface;

/**
 * AssetItem Value hydrator for value of type "Asset" & "Asset Collection".
 * It retrieves the labels of linked assets and add them into a value context for the frontend.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetValueHydrator implements ValueHydratorInterface
{
    /** @var FindAssetLabelsByCodesInterface */
    private $findAssetLabelsByCodes;

    public function __construct(FindAssetLabelsByCodesInterface $findAssetLabelsByCodes)
    {
        $this->findAssetLabelsByCodes = $findAssetLabelsByCodes;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof AssetAttribute || $attribute instanceof AssetCollectionAttribute;
    }

    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
        $assetIdentifiers = is_array($normalizedValue['data']) ? $normalizedValue['data'] : [$normalizedValue['data']];
        $data = array_values(array_intersect(array_keys($context['labels']), $assetIdentifiers));
        $labels = array_intersect_key($context['labels'], array_flip($assetIdentifiers));
        if ('asset' === $attribute->getType()) {
            if (empty($data)) {
                $normalizedValue['data'] = null;
            } else {
                $normalizedValue['data'] = $data[0];
            }
        } else {
            $normalizedValue['data'] = $data;
        }
        $normalizedValue['context']['labels'] = $labels;

        return $normalizedValue;
    }
}
