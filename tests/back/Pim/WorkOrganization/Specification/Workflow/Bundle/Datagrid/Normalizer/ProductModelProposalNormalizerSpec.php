<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelProposalNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormalizer,
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($standardNormalizer, $datagridNormalizer, $valueFactory, $attributeRepository);
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
        $valueFactory,
        $attributeRepository,
        ProductModelDraft $productModelProposal,
        WriteValueCollection $valueCollection,
        ProductModelInterface $productModel,
        ValueInterface $value,
        AttributeInterface $attribute
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
        $productModelProposal->getStatus()->willReturn(1);
        $productModelProposal->getEntityWithValue()->willReturn($productModel);
        $productModelProposal->getCreatedAt()->willReturn($created);
        $productModelProposal->getValues()->willReturn($valueCollection);

        $productModelProposal->getChanges()->willReturn($changes);
        $attributeRepository->findOneByIdentifier('text')->willReturn($attribute);
        $valueFactory->create($attribute, null, null, 'my text')->willReturn($value);
        $value->getAttributeCode()->willReturn('text');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $valueCollection = new ValueCollection();
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
