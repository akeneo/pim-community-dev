<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Api;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Api\QueryParametersChecker;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class QueryParametersCheckerSpec extends ObjectBehavior
{
    function let(
        QueryParametersCheckerInterface $queryParametersChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $queryParametersChecker,
            $authorizationChecker,
            $localeRepository,
            $attributeRepository,
            $categoryRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryParametersChecker::class);
    }

    function it_should_be_a_query_param_checker()
    {
        $this->shouldHaveType(QueryParametersCheckerInterface::class);
    }

    function it_raises_an_exception_on_locale_if_user_has_no_permissions(
        $localeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        LocaleInterface $enUsLocale,
        LocaleInterface $deDeLocale
    ) {
        $localeCodes = ['de_DE', 'en_US'];
        $queryParametersChecker->checkLocalesParameters($localeCodes, null)->shouldBeCalled();
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deDeLocale);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUsLocale)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $deDeLocale)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Locale "de_DE" does not exist or is not activated.'))
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_raises_an_exception_on_locales_if_user_has_no_permissions(
        $localeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        LocaleInterface $enUsLocale,
        LocaleInterface $deDeLocale
    ) {
        $localeCodes = ['de_DE', 'en_US'];
        $queryParametersChecker->checkLocalesParameters($localeCodes, null)->shouldBeCalled();
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deDeLocale);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUsLocale)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $deDeLocale)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Locales "de_DE, en_US" do not exist or are not activated.'))
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_checks_permissions_on_locales(
        $localeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        LocaleInterface $enUsLocale,
        LocaleInterface $deDeLocale
    ) {
        $localeCodes = ['de_DE', 'en_US'];
        $queryParametersChecker->checkLocalesParameters($localeCodes, null)->shouldBeCalled();
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deDeLocale);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUsLocale)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $deDeLocale)->willReturn(true);

        $this->shouldNotThrow(UnprocessableEntityHttpException::class)
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_raises_an_exception_on_attribute_if_user_has_no_permissions(
        $attributeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $queryParametersChecker->checkAttributesParameters($attributeCodes)->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn($attribute2);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup2)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Attribute "attribute_2" does not exist.'))
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_raises_an_exception_on_attributes_if_user_has_no_permissions(
        $attributeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $queryParametersChecker->checkAttributesParameters($attributeCodes)->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn($attribute2);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup1)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup2)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Attributes "attribute_1, attribute_2" do not exist.'))
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_checks_permissions_on_attributes(
        $attributeRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $queryParametersChecker->checkAttributesParameters($attributeCodes)->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn($attribute2);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup2)->willReturn(true);

        $this->shouldNotThrow(UnprocessableEntityHttpException::class)
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_raises_an_exception_on_category_if_user_has_no_permissions(
        $categoryRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $queryParametersChecker->checkCategoriesParameters($categories)->shouldBeCalled();
        $categoryRepository->findOneByIdentifier('category_1')->willReturn($category1);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn($category2);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category2)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Category "category_2" does not exist.'))
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_raises_an_exception_on_categories_if_user_has_no_permissions(
        $categoryRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $queryParametersChecker->checkCategoriesParameters($categories)->shouldBeCalled();
        $categoryRepository->findOneByIdentifier('category_1')->willReturn($category1);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn($category2);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category1)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category2)->willReturn(false);

        $this->shouldThrow(new UnprocessableEntityHttpException('Categories "category_1, category_2" do not exist.'))
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_checks_permissions_on_categories(
        $categoryRepository,
        $authorizationChecker,
        QueryParametersCheckerInterface $queryParametersChecker,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $queryParametersChecker->checkCategoriesParameters($categories)->shouldBeCalled();
        $categoryRepository->findOneByIdentifier('category_1')->willReturn($category1);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn($category2);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category2)->willReturn(true);

        $this->shouldNotThrow(UnprocessableEntityHttpException::class)
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_should_throw_an_exception_if_property_is_an_attribute_and_the_user_has_no_rights_on_it(
        $attributeRepository,
        $authorizationChecker,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup,
        QueryParametersCheckerInterface $queryParametersChecker
    ) {
        $queryParametersChecker->checkPropertyParameters('wrong_attribute', 'my_operator')->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('wrong_attribute')->willReturn($attribute);
        $attribute->getGroup()->willReturn($attributeGroup);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup)->willReturn(false);

        $this->shouldThrow(
            new UnprocessableEntityHttpException('Filter on property "wrong_attribute" is not supported or does not support operator "my_operator"')
        )
            ->during('checkPropertyParameters', ['wrong_attribute', 'my_operator']);
    }
}
