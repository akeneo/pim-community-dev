<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TransformerProcessorSpec extends ObjectBehavior
{
    function let(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        EntityTransformerInterface $transformer,
        ManagerRegistry $managerRegistry
    ) {
        $this->beConstructedWith(
            $validator,
            $translator,
            $transformer,
            $managerRegistry,
            'Pim\Bundle\CatalogBundle\Entity\Category'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor');
    }

    function it_is_an_item_processor_step_execution_aware()
    {
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_an_item(
        $transformer,
        $validator,
        CategoryInterface $category
    ) {
        $itemCategory = [
            'id'      => 10,
            'code'    => 'my_category',
            'created' => 'date'
        ];

        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $itemCategory)->willReturn($category);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate($category, [], $itemCategory, [])->willReturn([]);

        $this->process($itemCategory)->shouldReturn($category);
    }

    function it_processes_an_item_with_mapping(
        $transformer,
        $validator,
        CategoryInterface $category
    ) {
        $itemCategory = [
            'id'          => 10,
            'code'        => 'my_category',
            'created'     => 'date',
            'empty_value' => ''
        ];
        $mappedItemCategory = [
            'id'            => 10,
            'code'          => 'my_category',
            'Creation date' => 'date',
            'empty_value'   => ''
        ];

        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $mappedItemCategory)->willReturn($category);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate($category, [], $mappedItemCategory, [])->willReturn([]);

        $this->addMapping('created', 'Creation date');
        $this->process($itemCategory)->shouldReturn($category);
    }

    function it_processes_an_item_skipping_empty_values(
        $transformer,
        $validator,
        $translator,
        $managerRegistry,
        CategoryInterface $category
    ) {
        $this->beConstructedWith(
            $validator,
            $translator,
            $transformer,
            $managerRegistry,
            'Pim\Bundle\CatalogBundle\Entity\Category',
            true
        );

        $itemCategory = [
            'id'          => 10,
            'code'        => 'my_category',
            'created'     => 'date',
            'empty_value' => ''
        ];
        $mappedItemCategory = [
            'id'            => 10,
            'code'          => 'my_category',
            'Creation date' => 'date'
        ];

        $transformer->transform('Pim\Bundle\CatalogBundle\Entity\Category', $mappedItemCategory)->willReturn($category);
        $transformer->getErrors('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);
        $transformer->getTransformedColumnsInfo('Pim\Bundle\CatalogBundle\Entity\Category')->willReturn([]);

        $validator->validate($category, [], $mappedItemCategory, [])->willReturn([]);

        $this->addMapping('created', 'Creation date');
        $this->process($itemCategory)->shouldReturn($category);
    }
}
