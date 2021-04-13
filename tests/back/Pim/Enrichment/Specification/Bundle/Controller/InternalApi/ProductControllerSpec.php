<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductControllerSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        CursorableRepositoryInterface $cursorableRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        CollectionFilterInterface $productEditDataFilter,
        RemoverInterface $productRemover,
        ProductBuilderInterface $productBuilder,
        AttributeConverterInterface $localizedConverter,
        FilterInterface $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $constraintViolationNormalizer,
        ProductBuilderInterface $variantProductBuilder,
        AttributeFilterInterface $productAttributeFilter,
        RemoveParentInterface $removeParent,
        Client $productAndProductModelClient
    ): void {
        $this->beConstructedWith(
            $productRepository,
            $cursorableRepository,
            $attributeRepository,
            $productUpdater,
            $productSaver,
            $normalizer,
            $validator,
            $userContext,
            $objectFilter,
            $productEditDataFilter,
            $productRemover,
            $productBuilder,
            $localizedConverter,
            $emptyValuesFilter,
            $productValueConverter,
            $constraintViolationNormalizer,
            $variantProductBuilder,
            $productAttributeFilter,
            $removeParent,
            $productAndProductModelClient
        );
    }

    public function it_creates_a_new_product(
        Request $request,
        ProductBuilderInterface $productBuilder,
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        Client $productAndProductModelClient,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ): void {
        $product = new Product();
        $product->setIdentifier('banane');
        $request->isXmlHttpRequest()->willReturn(true);

        $request->getContent()->willReturn(json_encode(['identifier' => 'banane']));
        $productBuilder->createProduct('banane', null)->willReturn($product);
        $validator->validate($product)->willReturn(new ConstraintViolationList([]));

        $productSaver->save($product, ['origin' => ContextOrigin::UI])->shouldBeCalled();

        $normalizedProduct = [
            'identifier' => 'banane',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [['locale' => null, 'scope' => null, 'data' => 'banane']],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
        ];
        $userContext->toArray()->willReturn([]);
        $normalizer->normalize($product, 'internal_api', ['filter_types' => []])->willReturn($normalizedProduct);

        $this->createAction($request)->shouldbeLike(new JsonResponse($normalizedProduct));
    }

    public function it_updates_a_product(
        Request $request,
        ProductRepositoryInterface $productRepository,
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        AttributeConverterInterface $localizedConverter,
        ObjectUpdaterInterface $productUpdater,
        FilterInterface $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        CollectionFilterInterface $productEditDataFilter
    ): void {
        $product = new Product();
        $product->setIdentifier('banane');
        $request->isXmlHttpRequest()->willReturn(true);
        $data = [
            'identifier' => 'banane',
            'values' => [
                'sku' => [['locale' => null, 'scope' => null, 'data' => 'banane']],
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => 'A banana !']],
            ],
        ];

        $productRepository->find('1234')->willReturn($product);
        $objectFilter->filterObject($product, 'pim.internal_api.product.edit')->willReturn(false);
        $request->getContent()->willReturn(json_encode($data));
        $productEditDataFilter->filterCollection($data,  null, ['product' => $product])->willReturn($data);

        $productValueConverter->convert($data['values'])->willReturn($data['values']);

        $userContext->getUiLocale()->willReturn((new Locale())->setCode('en_US'));
        $localizedConverter->convertToDefaultFormats($data['values'], ['locale' => 'en_US'])->willReturn($data['values']);
        $emptyValuesFilter->filter($product, ['values' => $data['values']])->willReturn(['values' => $data['values']]);
        $productUpdater->update($product, $data)->shouldBeCalled();

        $validator->validate($product)->willReturn(new ConstraintViolationList([]));
        $localizedConverter->getViolations()->willReturn(new ConstraintViolationList([]));

        $productSaver->save($product, ['origin' => ContextOrigin::UI])->shouldBeCalled();
        $normalizedProduct = [
            'identifier' => 'banane',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [['locale' => null, 'scope' => null, 'data' => 'banane']],
                'description' => [['locale' => 'en_US', 'scope' => null, 'data' => 'A banana !']],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
        ];
        $userContext->toArray()->willReturn([]);
        $normalizer->normalize($product, 'internal_api', ['filter_types' => []])->willReturn($normalizedProduct);

        $this->postAction($request, '1234')->shouldbeLike(new JsonResponse($normalizedProduct));
    }

    public function it_removes_product(
        Request $request,
        Client $productAndProductModelClient,
        ProductRepositoryInterface $productRepository,
        RemoverInterface $productRemover
    ): void {
        $product = new Product();
        $product->setIdentifier('banane');
        $request->isXmlHttpRequest()->willReturn(true);

        $productRepository->find('1234')->willReturn($product);
        $productRemover->remove($product, ['origin' => ContextOrigin::UI])->shouldBeCalled();

        $this->removeAction($request, '1234')->shouldbeLike(new JsonResponse());
    }
}
