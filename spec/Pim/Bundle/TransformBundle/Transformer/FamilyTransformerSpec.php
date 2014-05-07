<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use PhpSpec\ObjectBehavior;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\TransformBundle\Transformer\EntityTransformerInterface;

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
