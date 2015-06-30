<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\FamilyBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FamilyUpdaterSpec extends ObjectBehavior
{
    function let(FamilyRepositoryInterface $familyRepository, FamilyBuilderInterface $familyBuilder)
    {
        $this->beConstructedWith($familyRepository, $familyBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\FamilyUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_family()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_family($familyBuilder, FamilyInterface $family, PropertyAccessor $accessor)
    {
        $values = [
            'code'                => 'mycode',
            'attributes'          => ['sku', 'name', 'description', 'price'],
            'attribute_as_label'  => 'name',
            'requirements'        => [
                'mobile' => ['sku', 'name'],
                'print'  => ['sku', 'name', 'description'],
            ],
            'labels'              => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $accessor->setValue($family, 'code', 'mycode');

        $familyBuilder->setLabels($family, ['fr_FR' => 'Moniteurs', 'en_US' => 'PC Monitors'])->shouldBeCalled();
        $familyBuilder->addAttributes($family, ['sku', 'name', 'description', 'price'])->shouldBeCalled();

        $familyBuilder->setAttributeRequirements($family, [
            'mobile' => ['sku', 'name'],
            'print'  => ['sku', 'name', 'description'],
        ])->shouldBeCalled();

        $familyBuilder->setAttributeAsLabel($family, 'name')->shouldBeCalled();


        $this->update($family, $values, []);
    }
}
