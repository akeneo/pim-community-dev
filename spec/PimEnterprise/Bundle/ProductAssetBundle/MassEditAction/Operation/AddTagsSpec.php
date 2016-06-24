<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;

class AddTagsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('add_tags_to_assets', 'pimee_product_asset_mass_add_tags');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pimee_product_asset_mass_add_tags');
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('add-tags-to-assets');
    }

    function it_provides_a_batch_job_code()
    {
        $this->getJobInstanceCode()->shouldReturn('add_tags_to_assets');
    }

    function it_stores_the_tags_to_add_to_add_to_the_assets(
        TagInterface $tagFoo,
        TagInterface $tagBar
    ) {
        $this->getTags()->shouldReturn([]);

        $this->setTags([$tagFoo, $tagBar]);

        $this->getTags()->shouldReturn([$tagFoo, $tagBar]);
        $this->getTags()->shouldBeArray();
    }

    function it_provides_correct_actions_to_apply_on_products(
        TagInterface $tagFoo,
        TagInterface $tagBar
    ) {
        $tagFoo->getCode()->willReturn('foo');
        $tagBar->getCode()->willReturn('bar');

        $this->setTags([$tagFoo, $tagBar]);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'tags',
                    'value' => ['foo', 'bar']
                ]
            ]
        );
    }

    function it_provides_formatted_batch_config_for_the_job(
        TagInterface $tagFoo,
        TagInterface $tagBar
    ) {
        $tagFoo->getCode()->willReturn('foo');
        $tagBar->getCode()->willReturn('bar');

        $this->setTags([$tagFoo, $tagBar]);

        $this->setFilters([
            ['id', 'IN', ['49', '2']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['49', '2']]],
                'actions' => [['field' => 'tags', 'value' => ['foo', 'bar']]]
            ]
        );
    }
}
