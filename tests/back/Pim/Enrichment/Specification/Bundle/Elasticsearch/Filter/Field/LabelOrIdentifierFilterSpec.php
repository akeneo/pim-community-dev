<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\LabelOrIdentifierFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * Label or identifier filter spec for an Elasticsearch query
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['label_or_identifier'],
            ['CONTAINS']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LabelOrIdentifierFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'CONTAINS'
        ]);
        $this->supportsOperator('CONTAINS')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_label_or_identifier_field()
    {
        $this->supportsField('label_or_identifier')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_without_locale_and_scope(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['wildcard' => ['identifier' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.<all_locales>' => '*book*']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('label_or_identifier', Operators::CONTAINS, 'book', null, null, []);
    }

    function it_adds_a_filter_with_operator_without_locale_but_with_scope(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['wildcard' => ['identifier' => '*book*']],
                        ['wildcard' => ['label.ecommerce.<all_locales>' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.<all_locales>' => '*book*']]
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('label_or_identifier', Operators::CONTAINS, 'book', null, 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_without_scope_but_with_locale(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['wildcard' => ['identifier' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.en_US' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.<all_locales>' => '*book*']]
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('label_or_identifier', Operators::CONTAINS, 'book', 'en_US', null, []);
    }

    function it_adds_a_filter_with_operator_with_scope_and_locale(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['wildcard' => ['identifier' => '*book*']],
                        ['wildcard' => ['label.ecommerce.en_US' => '*book*']],
                        ['wildcard' => ['label.ecommerce.<all_locales>' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.en_US' => '*book*']],
                        ['wildcard' => ['label.<all_channels>.<all_locales>' => '*book*']]
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('label_or_identifier', Operators::CONTAINS, 'book', 'en_US', 'ecommerce', []);
    }
}
