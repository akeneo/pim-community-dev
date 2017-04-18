<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Product completeness filter.
 * The operators "=", "!=", "<", "<=", ">" and ">=" are now deprecated in favor of more meaningful operators.
 * They are replaced respectively by:
 *      - "EQUALS ON AT LEAST ONE LOCALE"
 *      - "NOT EQUALS ON AT LEAST ONE LOCALE"
 *      - "LOWER THAN ON AT LEAST ONE LOCALE"
 *      - "LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE"
 *      - "GREATER THAN ON AT LEAST ONE LOCALE"
 *      - "GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE"
 *
 * @author    Julien Janvier <j.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var CachedObjectRepositoryInterface */
    protected $channelRepository;

    /**
     * @param CachedObjectRepositoryInterface $channelRepository
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        CachedObjectRepositoryInterface $channelRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->channelRepository = $channelRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY === $operator) {
            $clause = [
                'exists' => ['field' => 'completeness']
            ];
            $this->searchQueryBuilder->addMustNot($clause);

            return $this;
        }

        $this->checkChannelAndValue($field, $channel, $value);

        if (in_array(
            $operator,
            [
                Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES,
                Operators::GREATER_THAN_ON_ALL_LOCALES,
                Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES,
                Operators::LOWER_THAN_ON_ALL_LOCALES,
            ]
        )) {
            $this->checkOptions($field, $options);
            $localeCodes = $options['locales'];
        } else {
            $localeCodes = (null !== $locale) ? [$locale] : $this->getChannelByCode($channel)->getLocaleCodes();
        }


        /**
         * Example: product "SKU-001", for the channel "tablet", has the following complete ratios:
         *      50% for "en_US"
         *      50% for "fr_FR"
         *      50% for "it_IT"
         *
         * If we look for the products where the completeness != 50 on the channel tablet, then, the
         * product "SKU-001" should not be part of the results.
         *
         * To achieve that, we look for
         *      MUST NOT (50% for "completeness.tablet.en_US" AND 50% for "completeness.tablet.fr_FR" AND 50% for "completeness.tablet.it_IT") AND
         *      EXISTS "completeness.tablet.en_US" AND
         *      EXISTS "completeness.tablet.fr_FR" AND
         *      EXISTS "completeness.tablet.it_IT"
         */
        if (in_array($operator, [Operators::NOT_EQUAL, Operators::NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE])) {
            $filterClauses = [];
            foreach ($localeCodes as $localeCode) {
                $field = sprintf('completeness.%s.%s', $channel, $localeCode);
                $filterClauses[] = [
                    'term' => [
                        $field => $value,
                    ],
                ];

                $completenessExists = [
                    'exists' => [
                        'field' => $field,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($completenessExists);
            }

            $mustNotClause = [
                'bool' => [
                    'filter' => $filterClauses,
                ],
            ];
            $this->searchQueryBuilder->addMustNot($mustNotClause);

            return $this;
        }

        foreach ($localeCodes as $localeCode) {
            $field = sprintf('completeness.%s.%s', $channel, $localeCode);

            switch ($operator) {
                case Operators::EQUALS:
                case Operators::EQUALS_ON_AT_LEAST_ONE_LOCALE:
                    $clause = [
                        'term' => [
                            $field => $value
                        ]
                    ];
                    $this->searchQueryBuilder->addShould($clause);
                    break;
                case Operators::LOWER_THAN:
                case Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE:
                    $clause = [
                        'range' => [
                            $field => [
                                'lt' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addShould($clause);
                    break;
                case Operators::LOWER_THAN_ON_ALL_LOCALES:
                    $clause = [
                        'range' => [
                            $field => [
                                'lt' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addFilter($clause);
                    break;
                case Operators::GREATER_THAN:
                case Operators::GREATER_THAN_ON_AT_LEAST_ONE_LOCALE:
                    $clause = [
                        'range' => [
                            $field => [
                                'gt' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addShould($clause);
                    break;
                case Operators::GREATER_THAN_ON_ALL_LOCALES:
                    $clause = [
                        'range' => [
                            $field => [
                                'gt' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addFilter($clause);
                    break;
                case Operators::LOWER_OR_EQUAL_THAN:
                case Operators::LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE:
                    $clause = [
                        'range' => [
                            $field => [
                                'lte' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addShould($clause);
                    break;
                case Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES:
                    $clause = [
                        'range' => [
                            $field => [
                                'lte' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addFilter($clause);
                    break;
                case Operators::GREATER_OR_EQUAL_THAN:
                case Operators::GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE:
                    $clause = [
                        'range' => [
                            $field => [
                                'gte' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addShould($clause);
                    break;
                case Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES:
                    $clause = [
                        'range' => [
                            $field => [
                                'gte' => $value
                            ]
                        ]
                    ];
                    $this->searchQueryBuilder->addFilter($clause);
                    break;
                default:
                    throw InvalidOperatorException::notSupported($operator, static::class);
            }
        }

        return $this;
    }

    /**
     * Check if channel and value are valid
     *
     * @param string $field
     * @param string $channel
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkChannelAndValue($field, $channel, $value)
    {
        if (!is_numeric($value)) {
            throw InvalidPropertyTypeException::numericExpected($field, static::class, $value);
        }

        if (null === $channel) {
            throw InvalidPropertyException::dataExpected($field, 'a valid scope', static::class);
        }
    }

    /**
     * Check if options are valid for complex operators
     *      GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      GREATER_THAN_ON_ALL_LOCALES
     *      LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      LOWER_THAN_ON_ALL_LOCALES
     *
     * @param string $field
     * @param array  $options
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkOptions($field, array $options)
    {
        if (!array_key_exists('locales', $options)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $field,
                'locales',
                static::class,
                $options
            );
        }

        if (!isset($options['locales']) || !is_array($options['locales'])) {
            throw InvalidPropertyTypeException::arrayOfArraysExpected(
                $field,
                static::class,
                $options
            );
        }
    }

    /**
     * @param string $code
     *
     * @throws ObjectNotFoundException
     *
     * @return ChannelInterface
     */
    protected function getChannelByCode($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new ObjectNotFoundException(sprintf('Channel with "%s" code does not exist', $code));
        }

        return $channel;
    }
}
