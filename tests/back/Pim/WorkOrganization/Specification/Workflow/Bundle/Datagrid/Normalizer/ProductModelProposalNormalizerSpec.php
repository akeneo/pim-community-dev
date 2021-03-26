<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $this->beConstructedWith($standardNormalizer, $datagridNormalizer, $valueFactory, $getAttributesQuery);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_proposal_normalization(ProductModelDraft $productModelProposal)
    {
        $this->supportsNormalization($productModelProposal, 'datagrid')->shouldReturn(true);
    }

    function it_normalizes_a_product_model_draft(
        $standardNormalizer,
        $datagridNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery,
        ProductModelDraft $productModelProposal,
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        ValueInterface $value
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $changes = [
            'values' => [
                'text' => [['locale' => null, 'scope' => null, 'data' => 'my text']]
            ]
        ];
        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');

        $productModelProposal->getReviewStatusForChange('text', null, null)->willReturn('to_review');
        $productModelProposal->getId()->willReturn(1);
        $productModelProposal->getAuthor()->willReturn('Mary');
        $productModelProposal->getAuthorLabel()->willReturn('Mary Smith');
        $productModelProposal->getSource()->willReturn('pim');
        $productModelProposal->getSourceLabel()->willReturn('PIM');
        $productModelProposal->getStatus()->willReturn(1);
        $productModelProposal->getEntityWithValue()->willReturn($productModel);
        $productModelProposal->getCreatedAt()->willReturn($created);
        $productModelProposal->getValues()->willReturn($valueCollection);

        $productModelProposal->getChanges()->willReturn($changes);
        $attribute = new Attribute('text', 'pim_catalog_text', [], false, false, null, null, true, 'pim_catalog_text', []);
        $getAttributesQuery->forCode('text')->willReturn($attribute);
        $valueFactory->createByCheckingData($attribute, null, null, 'my text')->willReturn($value);
        $value->getAttributeCode()->willReturn('text');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $valueCollection = new WriteValueCollection();
        $valueCollection->add($value->getWrappedObject());
        $standardNormalizer->normalize(
            $valueCollection,
            'standard',
            $context
        )->willReturn(
            [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ],
                ]
            ]
        );

        $productModel->getCode()->willReturn('fake-spec-model');

        $this->normalize($productModelProposal, 'datagrid', $context)->shouldReturn(
            [
                'changes' => [
                    'text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'my text',
                        ],
                    ],
                ],
                'createdAt' => '2017-01-01',
                'product' => $productModel,
                'author' => 'Mary',
                'author_label' => 'Mary Smith',
                'source' => 'pim',
                'source_label' => 'PIM',
                'status' => 1,
                'proposal' => $productModelProposal,
                'search_id' => 'fake-spec-model',
                'id' => 'product_model_draft_1',
                'document_type' => 'product_model_draft',
                'proposal_id' => 1,
            ]
        );
    }

    function it_handles_empty_values_correctly(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery,
        ProductModelDraft $productModelDraft
    ) {
        $originalPM = new ProductModel();
        $originalPM->setCode('original');

        $createdAt = \DateTime::createFromFormat('Y-m-d', '2020-05-07');
        $datagridNormalizer->normalize($createdAt, 'datagrid', [])->willReturn('2020-05-07');

        $productModelDraft->getReviewStatusForChange('name', 'fr_FR', null)->willReturn('to_review');
        $productModelDraft->getCreatedAt()->willReturn($createdAt);
        $productModelDraft->getEntityWithValue()->willReturn($originalPM);
        $productModelDraft->getAuthor()->willReturn('john_doe');
        $productModelDraft->getAuthorLabel()->willReturn('John Doe');
        $productModelDraft->getSource()->willReturn('source');
        $productModelDraft->getSourceLabel()->willReturn('Source label');
        $productModelDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelDraft->getId()->willReturn(42);

        $productModelDraft->getChanges()->willReturn([
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => null],
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => 'changed'],
                ],
            ],
        ]);

        $nameAttribute = new Attribute('name', 'pim_catalog_text', [], true, false, null, null, null, 'string', []);
        $getAttributesQuery->forCode('name')->willReturn($nameAttribute);

        $valueFactory->createByCheckingData($nameAttribute, null, 'en_US', null)->shouldNotBeCalled();
        $valueFactory->createByCheckingData($nameAttribute, null, 'fr_FR', 'changed')
            ->shouldBeCalled()->willReturn(ScalarValue::localizableValue('name', 'changed', 'fr_FR'));

        $standardNormalizer->normalize(Argument::type(WriteValueCollection::class), 'standard', [])
            ->shouldBeCalled()->willReturn([
                'name' => [
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => 'changed'],
                ],
            ]);

        $this->normalize($productModelDraft, 'datagrid', [])->shouldReturn([
            'changes' => [
                'name' => [
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => 'changed'],
                    ['data' => null, 'locale' => 'en_US', 'scope' => null],
                ],
            ],
            'createdAt' => '2020-05-07',
            'product' => $originalPM,
            'author' => 'john_doe',
            'author_label' => 'John Doe',
            'source' => 'source',
            'source_label' => 'Source label',
            'status' => EntityWithValuesDraftInterface::READY,
            'proposal' => $productModelDraft,
            'search_id' => 'original',
            'id' => 'product_model_draft_42',
            'document_type' => 'product_model_draft',
            'proposal_id' => 42,
        ]);
    }
}
