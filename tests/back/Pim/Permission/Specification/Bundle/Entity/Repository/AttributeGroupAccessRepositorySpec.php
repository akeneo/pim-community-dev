<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Pim\Permission\Bundle\Entity\AttributeGroupAccess;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class AttributeGroupAccessRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = AttributeGroupAccess::class;
        $this->beConstructedWith($em, $class);
    }
}
