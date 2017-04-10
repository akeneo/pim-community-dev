<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Sorter;

use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

/**
 * Identifier sorter for an Elasticsearch query.
 *
 * As the identifier product value is not indexed in ES. Whenever you want to sort on the attribute identifier 'sku'
 * or on the field 'Identifier', the PQB will use this class which sorts on the field identifier.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierSorter implements FieldSorterInterface, AttributeSorterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedAttributes;

    /**
     * @param array $supportedFields
     * @param array $supportedAttributes
     */
    public function __construct(array $supportedFields = [], array $supportedAttributes = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedAttributes = $supportedAttributes;
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

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $channel = null)
    {
        $this->addFieldSorter('identifier', $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedAttributes);
    }
}
