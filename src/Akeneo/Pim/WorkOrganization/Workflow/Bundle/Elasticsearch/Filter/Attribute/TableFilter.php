<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Webmozart\Assert\Assert;

class TableFilter implements AttributeFilterInterface
{
    private ElasticsearchFilterValidator $filterValidator;
    private ?SearchQueryBuilder $searchQueryBuilder = null;

    public function __construct(ElasticsearchFilterValidator $filterValidator)
    {
        $this->filterValidator = $filterValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }
        Assert::same($attribute->getType(), AttributeTypes::TABLE);
        if (Operators::IS_NOT_EMPTY !== $operator) {
            throw InvalidOperatorException::notSupported($operator, static::class);
        }
        $this->checkLocaleAndChannel($attribute, $locale, $channel);

        $this->searchQueryBuilder->addFilter(
            [
                'exists' => [
                    'field' => \sprintf(
                        'values.%s-table.%s.%s',
                        $attribute->getCode(),
                        $channel ?? '<all_channels>',
                        $locale ?? '<all_locales>'
                    ),
                ],
            ]
        );

        return $this;
    }

    public function supportsAttribute(AttributeInterface $attribute): bool
    {
        return AttributeTypes::TABLE === $attribute->getType();
    }

    public function getAttributeTypes(): array
    {
        return [AttributeTypes::TABLE];
    }

    public function supportsOperator($operator): bool
    {
        return Operators::IS_NOT_EMPTY === $operator;
    }

    public function getOperators(): array
    {
        return [Operators::IS_NOT_EMPTY];
    }

    public function setQueryBuilder($searchQueryBuilder): void
    {
        if (!$searchQueryBuilder instanceof SearchQueryBuilder) {
            throw new \InvalidArgumentException(
                sprintf('Query builder should be an instance of "%s"', SearchQueryBuilder::class)
            );
        }

        $this->searchQueryBuilder = $searchQueryBuilder;
    }

    protected function checkLocaleAndChannel(AttributeInterface $attribute, $locale, $channel): void
    {
        try {
            $this->filterValidator->validateLocaleForAttribute($attribute->getCode(), $locale);
            $this->filterValidator->validateChannelForAttribute($attribute->getCode(), $channel);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }
}
