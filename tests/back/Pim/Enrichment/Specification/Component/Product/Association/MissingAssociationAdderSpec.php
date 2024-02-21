<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Association;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Association\AssociationClassResolver;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Prophecy\Argument;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingAssociationAdderSpec extends ObjectBehavior
{
    function let(
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
