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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesDetails implements FindAttributesDetailsInterface
{
    private InMemoryFindActivatedLocales $activatedLocalesQuery;
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        InMemoryFindActivatedLocales $activatedLocalesQuery,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->activatedLocalesQuery = $activatedLocalesQuery;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        //$activatedLocales = $this->activatedLocalesQuery->findAll();

        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);

        $attributeDetails = [];

        foreach ($attributes as $attribute) {
            $attributeDetail = new AttributeDetails();
            $attributeDetail->assetFamilyIdentifier = (string) $assetFamilyIdentifier;
            $attributeDetail->identifier = (string) $attribute->getIdentifier();
            $attributeDetail->code = (string) $attribute->getCode();
            $attributeDetail->isReadOnly = $attribute->normalize()['is_read_only'];
            $attributeDetail->isRequired = $attribute->normalize()['is_required'];
            $attributeDetail->labels = $attribute->normalize()['labels'];
            $attributeDetail->type = $attribute->getType();
            $attributeDetail->order = $attribute->getOrder()->intValue();
            $attributeDetail->valuePerChannel = $attribute->hasValuePerChannel();
            $attributeDetail->valuePerLocale = $attribute->hasValuePerLocale();
            $attributeDetail->additionalProperties = [];

            $attributeDetails[(string) $attribute->getCode()] = $attributeDetail;
        };

        return $attributeDetails;

        /*
        foreach ($this->results[$key] as $attributeDetails) {
            if (null !== $attributeDetails->labels) {
                $attributeDetails->labels = $this->getLabelsByActivatedLocale($attributeDetails->labels, $activatedLocales);
            }
        }

        return $this->results[$key];*/
    }

    private function getLabelsByActivatedLocale(array $labels, array $activatedLocales): array
    {
        $filteredLabels = [];
        foreach ($labels as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $filteredLabels[$localeCode] = $label;
            }
        }

        return $filteredLabels;
    }
}
