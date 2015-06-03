<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\ProductValue;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\FilterBundle\Form\Type\Filter\AjaxChoiceFilterType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility, UserContext $userContext, CustomAttributeRepository $repository)
    {
        $this->beConstructedWith($factory, $utility, $userContext, 'attributeOptionClass', $repository);

        $this->init(
            'foo',
            [
                ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_applies_choice_filter_on_datasource_for_array_value(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
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
        Collection $collection,
        $utility
    ) {
        $collection->count()->willReturn(2);
        $collection->getValues()->willReturn(['foo', 'bar']);
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
        $utility
    ) {
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
        $utility
    ) {
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
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
        $utility,
        $factory,
        $repository
    ) {
        $repository->findOneByCode('data_name_key')->willReturn($attribute);

        $factory->create(AjaxChoiceFilterType::NAME, [], [
            'csrf_protection' => false,
            'field_options' => [],
            'choice_url' => 'pim_ui_ajaxentity_list',
            'choice_url_params' => [
                'class' => 'attributeOptionClass',
                'dataLocale' => null,
                'collectionId' => null
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
