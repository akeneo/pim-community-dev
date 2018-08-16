<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;

/**
 * Field base sorter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFieldSorter implements FieldSorterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder;

    /** @var array */
    protected $supportedFields;

    /**
     * @param array $supportedFields
     */
    public function __construct(array $supportedFields = [])
    {
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($searchQueryBuilder)
    {
        if (!$searchQueryBuilder instanceof SearchQueryBuilder) {
            throw new \InvalidArgumentException(
                sprintf('Query builder should be an instance of "%s"', SearchQueryBuilder::class)
            );
        }

        $this->searchQueryBuilder = $searchQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        switch ($direction) {
            case Directions::ASCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            case Directions::DESCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }
}
