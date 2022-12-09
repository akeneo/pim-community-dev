<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesDetails implements FindAttributesDetailsInterface
{
    private array $attributesIndexedByReferenceEntityIdentifier = [];
    private array $attributesIndexedByIdentifier = [];

    public function __construct(
        private InMemoryFindActivatedLocales $activatedLocalesQuery,
    ) {
    }

    public function save(AttributeDetails $attributeDetails): void
    {
        $this->attributesIndexedByReferenceEntityIdentifier[(string) $attributeDetails->referenceEntityIdentifier][] = $attributeDetails;
        $this->attributesIndexedByIdentifier[(string) $attributeDetails->identifier] = $attributeDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $activatedLocales = $this->activatedLocalesQuery->findAll();
        $key = (string) $referenceEntityIdentifier;

        if (!isset($this->attributesIndexedByReferenceEntityIdentifier[$key])) {
            return [];
        }

        foreach ($this->attributesIndexedByReferenceEntityIdentifier[$key] as $attributeDetails) {
            if (null !== $attributeDetails->labels) {
                $attributeDetails->labels = $this->getLabelsByActivatedLocale($attributeDetails->labels, $activatedLocales);
            }
        }

        return $this->attributesIndexedByReferenceEntityIdentifier[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier(AttributeIdentifier $attributeIdentifier): ?AttributeDetails
    {
        return $this->attributesIndexedByIdentifier[(string) $attributeIdentifier] ?? null;
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
