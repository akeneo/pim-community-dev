<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Attribute;

use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;

/**
 * Proposal attribute filter for Elasticsearch
 */
abstract class AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder = null;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /** @var ProposalAttributePathResolver */
    protected $attributePathResolver;

    /** @var array */
    protected $supportedOperators = [];

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedAttributeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
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
     * Escapes particular values prior than doing a search query escaping whitespace or newlines.
     *
     * This is useful when using ES 'query_string' clauses in a search query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#_reserved_characters
     *
     * TODO: TIP-706 - This may move somewhere else
     *
     * @param string $value
     *
     * @return string
     */
    protected function escapeValue($value)
    {
        $regex = '#[-+=|! &(){}\[\]^"~*<>?:/\\\]#';

        return preg_replace($regex, '\\\$0', $value);
    }

    /**
     * Add a boolean clause
     *
     * @param array $clauses
     *
     * @return array
     */
    protected function addBooleanClause(array $clauses): array
    {
        return [
            'bool' => [
                'should' => $clauses,
                'minimum_should_match' => 1
            ]
        ];
    }
}
