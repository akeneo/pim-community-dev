<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Abstract attribute filter for Elasticsearch
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder = null;

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var string[] */
    protected $supportedAttributeTypes;

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
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndChannel(AttributeInterface $attribute, $locale, $channel)
    {
        try {
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $channel);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }

    /**
     * Calculates the ES path to a product value indexed in ES.
     *
     * TODO: TIP-706 - All this logic should be done somewhere else
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $channel
     *
     * @return string
     */
    protected function getAttributePath(AttributeInterface $attribute, $locale, $channel)
    {
        $locale = (null === $locale) ? '<all_locales>' : $locale;
        $channel = (null === $channel) ? '<all_channels>' : $channel;

        return 'values.' . $attribute->getCode() . '-' . $attribute->getBackendType() . '.' . $channel . '.' . $locale;
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
}
