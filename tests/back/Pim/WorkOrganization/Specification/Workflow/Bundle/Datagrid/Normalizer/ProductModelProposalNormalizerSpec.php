<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use PhpSpec\ObjectBehavior;
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

    function it_normalizes(
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
        )->willReturn([
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
}
