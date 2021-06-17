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
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAssetFamilyDetails implements FindAssetFamilyDetailsInterface
{
    /** @var AssetFamilyDetails[] */
    private array $results = [];

    private InMemoryFindActivatedLocales $activatedLocalesQuery;

    public function __construct(InMemoryFindActivatedLocales $activatedLocalesQuery)
    {
        $this->activatedLocalesQuery = $activatedLocalesQuery;
    }

    public function save(AssetFamilyDetails $assetFamilyDetails)
    {
        $key = $this->getKey($assetFamilyDetails->identifier);
        $this->results[$key] = $assetFamilyDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): ?AssetFamilyDetails {
        $key = $this->getKey($assetFamilyIdentifier);

        $activatedLocales = $this->activatedLocalesQuery->findAll();
        if (isset($this->results[$key])) {
            $this->results[$key]->labels = $this->getLabelsByActivatedLocale($this->results[$key]->labels, $activatedLocales);
        }

        return $this->results[$key] ?? null;
    }

    private function getKey(
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): string {
        return (string)$assetFamilyIdentifier;
    }

    private function getLabelsByActivatedLocale(LabelCollection $labels, array $activatedLocales): LabelCollection
    {
        $filteredLabels = [];
        foreach ($labels->normalize() as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $filteredLabels[$localeCode] = $label;
            }
        }

        return LabelCollection::fromArray($filteredLabels);
    }
}
