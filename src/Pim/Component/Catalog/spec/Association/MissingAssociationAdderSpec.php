<?php

namespace spec\Pim\Component\Catalog\Association;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Association\AssociationClassResolver;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\ProductModelAssociation;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Prophecy\Argument;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingAssociationAdderSpec extends ObjectBehavior
{
    public function let(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AssociationClassResolver $associationClassResolver
    ) {
        $this->beConstructedWith($associationTypeRepository, $associationClassResolver);
    }

    function it_adds_missing_associations(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AssociationClassResolver $associationClassResolver,
        AssociationTypeInterface $associationType,
        AssociationTypeInterface $associationType2,
        EntityWithAssociationsInterface $entity
    ) {
        $associationTypeRepository->findMissingAssociationTypes($entity)
            ->willReturn([$associationType, $associationType2]);

        $associationClassResolver->resolveAssociationClass($entity)
            ->willReturn(ProductModelAssociation::class);

        $entity->addAssociation(Argument::type(ProductModelAssociation::class))
            ->shouldBeCalledTimes(2);

        $this->addMissingAssociations($entity);
    }
}
