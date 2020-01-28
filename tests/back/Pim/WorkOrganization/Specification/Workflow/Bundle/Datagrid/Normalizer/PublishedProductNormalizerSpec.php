<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\PublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        NormalizerInterface $normalizer
    ) {
        $this->beConstructedWith($filter, $imageNormalizer, $getPublishedProductCompletenesses);
        $this->setNormalizer($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_a_published_product_normalizer()
    {
        $this->shouldHaveType(PublishedProductNormalizer::class);
    }

    function it_only_normalizes_published_products_for_datagrid_format()
    {
        $this->supportsNormalization(new PublishedProduct(), 'datagrid')->shouldReturn(true);
        $this->supportsNormalization(new PublishedProduct(), 'any_other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_published_product(
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer,
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        NormalizerInterface $normalizer,
        PublishedProductInterface $publishedProduct
    ) {
        $context = [
            'filter_types' => ['pim.transform.product_value.structured'],
            'data_locale' => 'en_US',
            'locales' => ['en_US'],
            'channels' => ['ecommerce'],
        ];

        $publishedProduct->getIdentifier()->willReturn('my_identifier');

        $family = new Family();
        $family->setCode('accessories');
        $family->setLocale('en_US');
        $family->setLabel('Accessories');
        $publishedProduct->getFamily()->willReturn($family);

        $group = new Group();
        $group->setCode('promotions');
        $group->setLocale('en_US');
        $group->setLabel('Promotions');
        $publishedProduct->getGroups()->willReturn(new ArrayCollection([$group]));

        $publishedProduct->isEnabled()->willReturn(true);

        $values = new WriteValueCollection();
        $filter->filterCollection($values, 'pim.transform.product_value.structured', $context)->willReturn(['filtered_values']);
        $normalizer->normalize(['filtered_values'], 'datagrid', $context)->willReturn(['normalized_values']);
        $publishedProduct->getValues()->willReturn($values);

        $dateTime = new \DateTime('2019-07-16');
        $normalizer->normalize($dateTime, 'datagrid', $context)->willReturn('2019-07-16T14:25:04+00:00');
        $publishedProduct->getCreated()->willReturn($dateTime);
        $publishedProduct->getUpdated()->willReturn($dateTime);

        $publishedProduct->getLabel('en_US', 'ecommerce')->willReturn('Superb Watch');

        $image = MediaValue::value('picture', new FileInfo());
        $imageNormalizer->normalize($image, 'en_US')->willReturn(['normalized_image']);
        $publishedProduct->getImage()->willReturn($image);

        $publishedProduct->getId()->willReturn(42);
        $completenesses = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, []),
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, ['name']),
            ]
        );
        $getPublishedProductCompletenesses->fromPublishedProductId(42)->willReturn($completenesses);

        $this->normalize(
            $publishedProduct,
            'datagrid',
            ['data_locale' => 'en_US', 'locales' => ['en_US'], 'channels' => ['ecommerce']]
        )
             ->shouldReturn(
                 [
                     'identifier' => 'my_identifier',
                     'family' => 'Accessories',
                     'groups' => 'Promotions',
                     'enabled' => true,
                     'values' => ['normalized_values'],
                     'created' => '2019-07-16T14:25:04+00:00',
                     'updated' => '2019-07-16T14:25:04+00:00',
                     'label' => 'Superb Watch',
                     'image' => ['normalized_image'],
                     'completeness' => 80,
                     'document_type' => 'product',
                     'technical_id' => 42,
                     'search_id' => 'product_42',
                     'is_checked' => false,
                     'complete_variant_product' => null,
                     'parent' => null,
                 ]
             );
    }
}
