<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalNormalizerSpec extends ObjectBehavior
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

    function it_supports_product_proposal_normalization(EntityWithValuesDraftInterface $productProposal)
    {
        $this->supportsNormalization($productProposal, 'datagrid')->shouldReturn(true);
    }

    function it_normalizes(
        $normalizer,
        CollectionFilterInterface $filter,
        EntityWithValuesDraftInterface $productProposal,
        ValueCollectionInterface $valueCollection,
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

        $productProposal->getId()->willReturn(1);
        $productProposal->getAuthor()->willReturn('Mary');
        $productProposal->getStatus()->willReturn(1);
        $productProposal->getEntityWithValue()->willReturn($product);
        $productProposal->getCreatedAt()->willReturn($created);
        $productProposal->getValues()->willReturn($valueCollection);

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
                'proposal_product' => $productProposal,
                'id' => 1,
                'identifier' => 1,
            ]
        );
    }
}
