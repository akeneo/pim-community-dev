<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;

/**
 * Family sorter for an Elasticsearch query.
 *
 * Sorting products on their family is done as following:
 * - Sort with the label corresponding to the given locale first.
 * - Then sort on the family code.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySorter extends BaseFieldSorter
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param array                     $supportedFields
     */
    public function __construct(LocaleRepositoryInterface $localeRepository, array $supportedFields = [])
    {
        parent::__construct($supportedFields);
        $this->localeRepository = $localeRepository;
    }

    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        $familyLabelPath = null;

        if (null !== $locale && !in_array($locale, $this->localeRepository->getActivatedLocaleCodes())) {
            throw new \InvalidArgumentException(
                sprintf('Expects a valid locale code to filter on family labels. "%s" given.', $locale)
            );
        } elseif (null !== $locale) {
            $familyLabelPath = 'family.labels.' . $locale;
        }

        switch ($direction) {
            case Directions::ASCENDING:
                if (null !== $familyLabelPath) {
                    $sortFamilyLabelClause = [
                        $familyLabelPath => [
                            'order'         => 'ASC',
                            'unmapped_type' => 'string',
                            'missing'       => '_last',
                        ],
                    ];
                    $this->searchQueryBuilder->addSort($sortFamilyLabelClause);
                }

                $sortFamilyCodeClause = [
                    'family.code' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortFamilyCodeClause);

                break;
            case Directions::DESCENDING:
                if (null !== $familyLabelPath) {
                    $sortFamilyLabelClause = [
                        $familyLabelPath => [
                            'order'         => 'DESC',
                            'unmapped_type' => 'string',
                            'missing'       => '_last',
                        ],
                    ];
                    $this->searchQueryBuilder->addSort($sortFamilyLabelClause);
                }

                $sortFamilyCodeClause = [
                    'family.code' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ];

                $this->searchQueryBuilder->addSort($sortFamilyCodeClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }
}
