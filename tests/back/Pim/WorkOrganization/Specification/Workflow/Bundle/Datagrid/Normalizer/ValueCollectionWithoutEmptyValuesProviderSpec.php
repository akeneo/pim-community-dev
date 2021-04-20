<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValueCollectionWithoutEmptyValuesProviderSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith(
            $valueFactory,
            $standardNormalizer,
            $getAttributes
        );
    }

    function it_get_changes(
        NormalizerInterface $standardNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributes,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        ValueInterface $value
    ) {
        $context = ['locales' => ['en_US']];
        $change = [
            'data' => 'proposal data',
            'scope' => null,
            'locale' => null,
        ];

        $entityWithValuesDraft->getId()->willReturn(42);
        $entityWithValuesDraft->isInProgress()->willReturn(false);
        $entityWithValuesDraft->getEntityWithValue()->willReturn($entityWithValues);
        $entityWithValuesDraft->getChanges()->willReturn(['values' => ['name' => [$change]]]);
        $entityWithValuesDraft->getAuthor()->willReturn('mary');
        $entityWithValues->getIdentifier()->willReturn('product_69');
        $entityWithValuesDraft->getReviewStatusForChange('name', null, null)->willReturn('to_review');

        $getAttribute = new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            null,
            null,
            'text',
            []
        );
        $getAttributes->forCode('name')->willReturn($getAttribute);

        $value->getAttributeCode()->willReturn('name');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $valueFactory->createByCheckingData($getAttribute, null, null, 'proposal data')->willReturn($value);

        $standardNormalizer->normalize(Argument::type(WriteValueCollection::class), 'standard', $context)->willReturn([
            'name' => [$change]
        ]);

        $this->getChanges($entityWithValuesDraft, $context)->shouldReturn([
            'name' => [
                [
                    'data' => 'proposal data',
                    'scope' => null,
                    'locale' => null,
                ],
            ]
        ]);
    }
}
