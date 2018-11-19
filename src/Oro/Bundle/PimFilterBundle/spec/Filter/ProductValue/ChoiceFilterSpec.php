<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\AjaxChoiceFilterType;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        UserContext $userContext,
        CustomAttributeRepository $attributeRepository,
        CustomAttributeOptionRepository $attributeOptionRepository
    )
    {
        $this->beConstructedWith($factory, $utility, $userContext, $attributeRepository, $attributeOptionRepository);

        $this->init(
            'foo',
            [
                ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(ChoiceFilter::class);
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_applies_choice_filter_on_datasource_for_array_value(
        FilterDatasourceAdapterInterface $datasource,
        AttributeInterface $attribute,
        $utility,
        $attributeRepository,
        $attributeOptionRepository
    ) {
        $attributeRepository->findOneByCode('data_name_key')->willReturn($attribute);
        $attributeOptionRepository->findCodesByIdentifiers(Argument::any(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);
        $utility->applyFilter($datasource, 'data_name_key', 'IN', ['foo', 'bar'])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => AjaxChoiceFilterType::TYPE_CONTAINS,
            ]
        );
    }

    function it_applies_choice_filter_on_datasource_for_collection_value(
        FilterDatasourceAdapterInterface $datasource,
        AttributeInterface $attribute,
        Collection $collection,
        $utility,
        $attributeRepository,
        $attributeOptionRepository
    ) {
        $attributeRepository->findOneByCode('data_name_key')->willReturn($attribute);

        $collection->count()->willReturn(2);
        $collection->getValues()->willReturn(['foo', 'bar']);

        $attributeOptionRepository->findCodesByIdentifiers(Argument::any(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);

        $utility->applyFilter($datasource, 'data_name_key', 'IN', ['foo', 'bar'])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => $collection,
                'type'  => AjaxChoiceFilterType::TYPE_CONTAINS,
            ]
        );
    }

    function it_applies_choice_filter_on_datasource_for_array_value_with_not_contains_type(
        FilterDatasourceAdapterInterface $datasource,
        AttributeInterface $attribute,
        $utility,
        $attributeRepository,
        $attributeOptionRepository
    ) {
        $attributeRepository->findOneByCode('data_name_key')->willReturn($attribute);
        $attributeOptionRepository->findCodesByIdentifiers(Argument::any(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);

        $utility->applyFilter($datasource, 'data_name_key', 'NOT IN', ['foo', 'bar'])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => AjaxChoiceFilterType::TYPE_NOT_CONTAINS,
            ]
        );
    }

    function it_falbacks_on_contains_type_if_type_is_unknown(
        FilterDatasourceAdapterInterface $datasource,
        AttributeInterface $attribute,
        $utility,
        $attributeRepository,
        $attributeOptionRepository
    ) {
        $attributeRepository->findOneByCode('data_name_key')->willReturn($attribute);
        $attributeOptionRepository->findCodesByIdentifiers(Argument::any(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);

        $utility->applyFilter($datasource, 'data_name_key', 'IN', ['foo', 'bar'])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['foo', 'bar'],
                'type'  => 'unknown',
            ]
        );
    }

    /**
     * TODO The getForm method is obviously too smart here
     */
    function it_provides_a_choice_filter_form(
        Form $form,
        AttributeInterface $attribute,
        $factory,
        $attributeRepository,
        $attributeOptionRepository,
        $userContext
    ) {
        $attributeRepository->findOneByCode('data_name_key')->willReturn($attribute);
        $attributeOptionRepository->getClassName()->willReturn('attributeOptionClass');
        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $factory->create(AjaxChoiceFilterType::class, [], [
            'csrf_protection'   => false,
            'choice_url'        => 'pim_ui_ajaxentity_list',
            'choice_url_params' => [
                'class'        => 'attributeOptionClass',
                'dataLocale'   => 'en_US',
                'collectionId' => null,
                'options'      => [
                    'type' => 'code',
                ],
            ]
        ])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }
}

class CustomAttributeRepository extends AttributeRepository
{
    function findOneByCode()
    {
        return null;
    }
}

class CustomAttributeOptionRepository extends AttributeOptionRepository
{
    function findCodesByIdentifiers($code, array $optionCodes)
    {
        return null;
    }
}
