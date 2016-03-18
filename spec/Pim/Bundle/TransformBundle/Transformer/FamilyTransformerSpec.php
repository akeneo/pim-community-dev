<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class FamilyTransformerSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        FamilyFactory $factory
    ) {
        $requirementClass = 'Pim\Bundle\CatalogBundle\Entity\AttributeRequirement';
        $this->beConstructedWith(
            $doctrine,
            $propertyAccessor,
            $guesser,
            $columnInfoTransformer,
            $transformerRegistry,
            $factory,
            $requirementClass
        );
    }

    function it_is_a_nested_entity_transformer()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\NestedEntityTransformer');
    }
}
