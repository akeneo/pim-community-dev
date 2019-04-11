<?php

namespace spec\Akeneo\Tool\Bundle\ApiBundle\Checker;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersChecker;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class QueryParametersCheckerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $localeRepository,
            $attributeRepository,
            $categoryRepository,
            ['family', 'enabled', 'groups', 'categories', 'completeness']
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

    function it_raises_an_exception_if_a_locale_does_not_exist($localeRepository, LocaleInterface $enUsLocale)
    {
        $localeCodes = ['de_DE', 'en_US'];
        $enUsLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn(null);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $this->shouldThrow(new UnprocessableEntityHttpException('Locale "de_DE" does not exist or is not activated.'))
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_raises_an_exception_if_a_locale_is_not_activated($localeRepository, LocaleInterface $enUsLocale)
    {
        $localeCodes = ['de_DE', 'en_US'];
        $enUsLocale->isActivated()->willReturn(false);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn(null);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $this->shouldThrow(new UnprocessableEntityHttpException('Locales "de_DE, en_US" do not exist or are not activated.'))
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_raises_an_exception_if_locales_do_not_exist($localeRepository)
    {
        $localeCodes = ['de_DE', 'en_US'];
        $localeRepository->findOneByIdentifier('de_DE')->willReturn(null);
        $localeRepository->findOneByIdentifier('en_US')->willReturn(null);

        $this->shouldThrow(new UnprocessableEntityHttpException('Locales "de_DE, en_US" do not exist or are not activated.'))
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_should_not_raise_an_exception_if_a_locale_exist(
        $localeRepository,
        LocaleInterface $enUsLocale,
        LocaleInterface $deDeLocale
    ) {
        $localeCodes = ['de_DE', 'en_US'];
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($deDeLocale);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUsLocale);

        $this->shouldNotThrow('UnprocessableEntityHttpException')
            ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    function it_raises_an_exception_if_an_attribute_does_not_exist(
        $attributeRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn(null);

        $this->shouldThrow(new UnprocessableEntityHttpException('Attribute "attribute_2" does not exist.'))
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_raises_an_exception_if_attributes_do_not_exist(
        $attributeRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn(null);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn(null);

        $this->shouldThrow(new UnprocessableEntityHttpException('Attributes "attribute_1, attribute_2" do not exist.'))
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_should_not_raise_an_exception_if_attribute_exist(
        $attributeRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeGroupInterface $attributeGroup1,
        AttributeGroupInterface $attributeGroup2
    ) {
        $attributeCodes = ['attribute_1', 'attribute_2'];

        $attribute1->getGroup()->willReturn($attributeGroup1);
        $attribute2->getGroup()->willReturn($attributeGroup2);

        $attributeRepository->findOneByIdentifier('attribute_1')->willReturn($attribute1);
        $attributeRepository->findOneByIdentifier('attribute_2')->willReturn($attribute2);

        $this->shouldNotThrow('UnprocessableEntityHttpException')
            ->during('checkAttributesParameters', [$attributeCodes]);
    }

    function it_raises_an_exception_if_a_category_does_not_exist(
        $categoryRepository,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $categoryRepository->findOneByIdentifier('category_1')->willReturn($category1);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn(null);

        $this->shouldThrow(new UnprocessableEntityHttpException('Category "category_2" does not exist.'))
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_raises_an_exception_if_categories_do_not_exist(
        $categoryRepository,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $categoryRepository->findOneByIdentifier('category_1')->willReturn(null);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn(null);

        $this->shouldThrow(new UnprocessableEntityHttpException('Categories "category_1, category_2" do not exist.'))
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_should_not_raise_an_exception_if_a_category_exist(
        $categoryRepository,
        CategoryInterface $category1,
        CategoryInterface $category2
    ) {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];

        $category1->getCode()->willReturn('category_1');
        $category2->getCode()->willReturn('category_2');

        $categoryRepository->findOneByIdentifier('category_1')->willReturn($category1);
        $categoryRepository->findOneByIdentifier('category_2')->willReturn($category2);

        $this->shouldNotThrow('UnprocessableEntityHttpException')
            ->during('checkCategoriesParameters', [$categories]);
    }

    function it_should_throw_an_exception_if_json_is_null()
    {
        $this->shouldThrow(new BadRequestHttpException('Search query parameter should be valid JSON.'))
            ->during('checkCriterionParameters', ['']);
    }

    function it_should_throw_an_exception_if_it_is_not_correctly_structured()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Structure of filter "categories" should respect this structure: {"categories":[{"operator": "my_operator", "value": "my_value"}]}')
        )
            ->during('checkCriterionParameters', ['{"categories":[]}']);
    }

    function it_should_throw_an_exception_if_operator_is_missing()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Operator is missing for the property "categories".')
        )
            ->during('checkCriterionParameters', ['{"categories":[{"value": "my_value"}]}']);
    }

    function it_should_throw_an_exception_if_property_is_not_a_product_filter_or_an_attribute()
    {
        $this->shouldThrow(
            new UnprocessableEntityHttpException('Filter on property "wrong_attribute" is not supported or does not support operator "my_operator"')
        )
            ->during('checkPropertyParameters', ['wrong_attribute', 'my_operator']);
    }
}
