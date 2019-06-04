<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer, NormalizerInterface $datagridNormalizer)
    {
        $this->beConstructedWith($standardNormalizer, $datagridNormalizer);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_proposal_normalization(ProductDraft $productProposal)
    {
        $this->supportsNormalization($productProposal, 'datagrid')->shouldReturn(true);
    }

    function it_normalizes(
        $standardNormalizer,
        $datagridNormalizer,
        CollectionFilterInterface $filter,
        ProductDraft $productProposal,
        WriteValueCollection $valueCollection,
        ProductInterface $product
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
        $standardNormalizer->normalize($valueCollection, 'standard', $context)->willReturn(
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
        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');

        $productProposal->getId()->willReturn(1);
        $productProposal->getAuthor()->willReturn('Mary');
        $productProposal->getStatus()->willReturn(1);
        $productProposal->getEntityWithValue()->willReturn($product);
        $productProposal->getCreatedAt()->willReturn($created);
        $productProposal->getValues()->willReturn($valueCollection);
        $product->getIdentifier()->willReturn(2);

        $this->normalize($productProposal, 'datagrid', $context)->shouldReturn(
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
                'product' => $product,
                'author' => 'Mary',
                'status' => 1,
                'proposal' => $productProposal,
                'search_id' => 2,
                'id' => 'product_draft_1',
                'document_type' => 'product_draft',
                'proposal_id' => 1,
            ]
        );
    }
}
