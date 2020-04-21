<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductProposalNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormalizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($standardNormalizer, $datagridNormalizer, $valueFactory, $getAttributes);
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
        ValueFactory $valueFactory,
        GetAttributes $getAttributes,
        ProductDraft $productProposal,
        WriteValueCollection $valueCollection,
        ProductInterface $product,
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
                'text' => [['locale' => null, 'scope' => null, 'data' => 'my text']],
                'description' => [['locale' => null, 'scope' => null, 'data' => null]]
            ]
        ];
        $datagridNormalizer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01');

        $productProposal->getReviewStatusForChange('text', null, null)->willReturn('to_review');
        $productProposal->getId()->willReturn(1);
        $productProposal->getAuthor()->willReturn('Mary');
        $productProposal->getAuthorLabel()->willReturn('Mary Smith');
        $productProposal->getSource()->willReturn('pim');
        $productProposal->getSourceLabel()->willReturn('PIM');
        $productProposal->getStatus()->willReturn(1);
        $productProposal->getEntityWithValue()->willReturn($product);
        $productProposal->getCreatedAt()->willReturn($created);
        $productProposal->getValues()->willReturn($valueCollection);
        $productProposal->getChanges()->willReturn($changes);

        $textAttribute = new Attribute('text', 'pim_catalog_text', [], false, false, null, null, false, 'pim_catalog_text', []);
        $descriptionAttribute = new Attribute('description', 'pim_catalog_text', [], false, false, null, null, false, 'pim_catalog_text', []);

        $getAttributes->forCode('text')->willReturn($textAttribute);
        $getAttributes->forCode('description')->willReturn($descriptionAttribute);

        $valueFactory->createByCheckingData($textAttribute, null, null, 'my text')->willReturn($value);
        $valueFactory->createByCheckingData($descriptionAttribute, Argument::cetera())->shouldNotBeCalled();
        $value->getAttributeCode()->willReturn('text');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $product->getIdentifier()->willReturn(2);
        $valueCollection = new WriteValueCollection();
        $valueCollection->add($value->getWrappedObject());
        $standardNormalizer->normalize(
            $valueCollection,
            'standard',
            $context
        )->willReturn([
            'text' => [['locale' => null, 'scope'  => null, 'data'   => 'my text']],
        ]);

        $this->normalize($productProposal, 'datagrid', $context)->shouldReturn(
            [
                'changes' => [
                    'text' => [['locale' => null, 'scope'  => null, 'data'   => 'my text']],
                    'description' => [['data' => null, 'locale' => null, 'scope' => null]],
                ],
                'createdAt' => '2017-01-01',
                'product' => $product,
                'author' => 'Mary',
                'author_label' => 'Mary Smith',
                'source' => 'pim',
                'source_label' => 'PIM',
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
