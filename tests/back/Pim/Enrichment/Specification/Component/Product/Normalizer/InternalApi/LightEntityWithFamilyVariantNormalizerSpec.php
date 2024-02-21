<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\LightEntityWithFamilyVariantNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LightEntityWithFamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(
        ImageNormalizer $imageNormalizer,
        ImageAsLabel $imageAsLabel,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        GetProductCompletenessRatio $getCompletenessRatio,
        VariantProductRatioInterface $variantProductRatioQuery,
        AxisValueLabelsNormalizer $axisValueLabelsNormalizer
    ) {
        $color = (new Attribute())->setCode('color')->setType(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attributesProvider->getAxes(Argument::type(EntityWithFamilyVariantInterface::class))
                           ->willReturn([$color]);

        $green = (new AttributeOption())->setCode('green')->setSortOrder(5)->setAttribute($color);
        $attributeOptionRepository->findOneByIdentifier('color.green')->willReturn($green);

        $axisValueLabelsNormalizer->supports(AttributeTypes::OPTION_SIMPLE_SELECT)->willReturn(true);
        $axisValueLabelsNormalizer->normalize(Argument::type(ValueInterface::class), 'en_US')->willReturn('Green');

        $this->beConstructedWith(
            $imageNormalizer,
            $imageAsLabel,
            $attributesProvider,
            $attributeOptionRepository,
            $getCompletenessRatio,
            $variantProductRatioQuery,
            [$axisValueLabelsNormalizer]
        );
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LightEntityWithFamilyVariantNormalizer::class);
    }

    function it_only_suports_entities_with_family_variant_and_internal_api_format()
    {
        $this->supportsNormalization(new Product(), 'internal_api')->shouldReturn(true);
        $this->supportsNormalization(new ProductModel(), 'internal_api')->shouldReturn(true);

        $this->supportsNormalization(new Product(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new AttributeOption(), 'internal_api')->shouldReturn(false);
    }

    function it_throws_an_exception_if_channel_is_not_provided_in_the_context()
    {
        $this->shouldThrow(new \LogicException('channel and locale have to be defined in the $context argument'))
             ->during('normalize', [new ProductModel(), 'internal_api', ['locale' => 'en_US']]);
    }

    function it_throws_an_exception_if_locale_is_not_provided_in_the_context()
    {
        $this->shouldThrow(new \LogicException('channel and locale have to be defined in the $context argument'))
             ->during('normalize', [new ProductModel(), 'internal_api', ['channel' => 'ecommerce']]);
    }

    function it_normalizes_a_variant_product(
        ImageNormalizer $imageNormalizer,
        GetProductCompletenessRatio $getCompletenessRatio,
        ProductInterface $variantProduct,
        FileInfoInterface $fileInfo
    ) {
        $variantProduct->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $variantProduct->getIdentifier()->willReturn('tshirt_green');
        $variantProduct->getLabel('en_US', 'ecommerce')->willReturn('Green t-shirt');

        $imageValue = MediaValue::value('image', $fileInfo->getWrappedObject());
        $imageNormalizer->normalize($imageValue)->willReturn(
            [
                'filePath' => '1/2/3/my_variant_product.png',
                'originalFilename' => 'my_variant_product.png',
            ]
        );
        $variantProduct->getImage()->willReturn($imageValue);
        $variantProduct->getValue('color')->willReturn(OptionValue::value('color', 'green'));

        $getCompletenessRatio->forChannelCodeAndLocaleCode(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), 'ecommerce', 'en_US')->willReturn(44);

        $this->normalize($variantProduct, 'internal_api', ['channel' => 'ecommerce', 'locale' => 'en_US'])->shouldReturn(
             [
                 'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                 'identifier' => 'tshirt_green',
                 'labels' => ['en_US' => 'Green t-shirt'],
                 'axes_values_labels' => ['en_US' => 'Green'],
                 'order' => [5, 'green'],
                 'image' => [
                     'filePath' => '1/2/3/my_variant_product.png',
                     'originalFilename' => 'my_variant_product.png',
                 ],
                 'model_type' => 'product',
                 'completeness' => [
                     [
                         'channel' => 'ecommerce',
                         'locales' => [
                             'en_US' => [
                                 'completeness' => [
                                     'ratio' => 44,
                                 ],
                             ],
                         ],
                     ],
                 ],
             ]
         );
    }

    function it_normalizes_a_sub_product_model(
        ImageNormalizer $imageNormalizer,
        ImageAsLabel $imageAsLabel,
        VariantProductRatioInterface $variantProductRatioQuery,
        ProductModelInterface $productModel,
        FileInfoInterface $fileInfo
    ) {
        $productModel->getId()->willReturn(56);
        $productModel->getIdentifier()->willReturn('my_tshirt_model');
        $productModel->getLabel('en_US', 'ecommerce')->willReturn('Green t-shirt model');
        $productModel->getValue('color')->willReturn(OptionValue::value('color', 'green'));

        $imageValue = MediaValue::value('model_picture', $fileInfo->getWrappedObject());
        $imageNormalizer->normalize($imageValue)->willReturn([
            'filePath' => 'a/b/c/my_model.jpg',
            'originalFilename' => 'my_model.jpg',
        ]);
        $imageAsLabel->value($productModel)->willReturn($imageValue);

        $variantProductRatioQuery->findComplete($productModel)->willReturn(new CompleteVariantProducts(
            [
                [
                    'product_uuid' => 'fef4e0bd-63e1-4eba-b89a-8298ab895d78',
                    'channel_code' => 'ecommerce',
                    'locale_code' => 'en_US',
                    'complete' => 0,
                ],
                [
                    'product_uuid' => '0285ef68-6d73-4591-bc29-510985834e87',
                    'channel_code' => 'ecommerce',
                    'locale_code' => 'en_US',
                    'complete' => 1,
                ],
                [
                    'product_uuid' => '4bda4603-dc11-4754-9934-1105079e5aa6',
                    'channel_code' => 'ecommerce',
                    'locale_code' => 'en_US',
                    'complete' => 1,
                ],
            ]
        ));

        $this->normalize($productModel, 'internal_api', ['channel' => 'ecommerce', 'locale' => 'en_US'])->shouldReturn(
            [
                'id' => 56,
                'identifier' => 'my_tshirt_model',
                'labels' => ['en_US' => 'Green t-shirt model'],
                'axes_values_labels' => ['en_US' => 'Green'],
                'order' => [5, 'green'],
                'image' => [
                    'filePath' => 'a/b/c/my_model.jpg',
                    'originalFilename' => 'my_model.jpg',
                ],
                'model_type' => 'product_model',
                'completeness' => [
                    'completenesses' => [
                        'ecommerce' => [
                            'en_US' => 2,
                        ],
                    ],
                    'total' => 3,
                ],

            ]
        );

    }
}
