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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesDetails implements FindAttributesDetailsInterface
{
    private $results = [];

    /** @var InMemoryFindActivatedLocales */
    private $activatedLocalesQuery;

    public function __construct(InMemoryFindActivatedLocales $activatedLocalesQuery)
    {
        $this->activatedLocalesQuery = $activatedLocalesQuery;
    }

    public function save(AttributeDetails $assetFamilyDetails): void
    {
        $this->results[(string) $assetFamilyDetails->assetFamilyIdentifier][] = $assetFamilyDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $activatedLocales = $this->activatedLocalesQuery->findAll();
        $key = (string) $assetFamilyIdentifier;

        if (!isset($this->results[$key])) {
            return [];
        }

        foreach ($this->results[$key] as $attributeDetails) {
            if (null !== $attributeDetails->labels) {
                $attributeDetails->labels = $this->getLabelsByActivatedLocale($attributeDetails->labels, $activatedLocales);
            }
        }

        return $this->results[$key];
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
