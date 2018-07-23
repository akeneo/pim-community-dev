<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $normalizer->implement(NormalizerInterface::class);
        $this->setNormalizer($normalizer);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(NormalizerAwareInterface::class);
    }

    function it_supports_product_proposal_normalization(ProductModelDraft $productModelProposal)
    {
        $this->supportsNormalization($productModelProposal, 'datagrid')->shouldReturn(true);
    }

    function it_normalizes(
        $normalizer,
        CollectionFilterInterface $filter,
        ProductModelDraft $productModelProposal,
        ValueCollectionInterface $valueCollection,
        ProductModelInterface $productModel
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'locales'      => ['en_US'],
            'channels'     => ['ecommerce'],
            'data_locale'  => 'en_US',
        ];

        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $filter->filterCollection($valueCollection, 'pim.transform.product_value.structured', $context)
            ->willReturn($valueCollection);
        $normalizer->normalize($valueCollection, 'standard', $context)->willReturn(
            [
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'my text',
                    ],
                ],
            ]
        );
        $normalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');

        $productModelProposal->getId()->willReturn(1);
        $productModelProposal->getAuthor()->willReturn('Mary');
        $productModelProposal->getStatus()->willReturn(1);
        $productModelProposal->getEntityWithValue()->willReturn($productModel);
        $productModelProposal->getCreatedAt()->willReturn($created);
        $productModelProposal->getValues()->willReturn($valueCollection);
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
