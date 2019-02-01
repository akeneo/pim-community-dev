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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;

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

    public function save(AttributeDetails $referenceEntityDetails): void
    {
        $this->results[(string) $referenceEntityDetails->referenceEntityIdentifier][] = $referenceEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $activatedLocales = ($this->activatedLocalesQuery)();
        $key = (string) $referenceEntityIdentifier;

        if (isset($this->results[$key])) {
            foreach ($this->results[$key] as $attributeDetails) {
                if (null !== $attributeDetails->labels) {
                    $attributeDetails->labels = $this->getLabelsByActivatedLocale($attributeDetails->labels, $activatedLocales);
                }
            }
        }

        return $this->results[$key] ?? [];
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
