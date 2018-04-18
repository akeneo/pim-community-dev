<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Association;

use Pim\Component\Catalog\Model\AssociationAwareInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

/**
 * Create all missing associations for each existing association type
 * and add them to an association aware entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingAssociationAdder
{
    /** @var AssociationTypeRepositoryInterface */
    private $associationTypeRepository;

    /** @var AssociationClassResolver */
    private $associationClassResolver;

    /**
     * @param AssociationTypeRepositoryInterface $associationTypeRepository
     * @param AssociationClassResolver           $associationClassResolver
     */
    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AssociationClassResolver $associationClassResolver
    ) {
        $this->associationTypeRepository = $associationTypeRepository;
        $this->associationClassResolver = $associationClassResolver;
    }

    /**
     * @param AssociationAwareInterface $entity
     */
    public function addMissingAssociations(AssociationAwareInterface $entity)
    {
        $missingAssocTypes = $this->associationTypeRepository->findMissingAssociationTypes($entity);

        if (!empty($missingAssocTypes)) {
            foreach ($missingAssocTypes as $associationType) {
                $associationClass = $this->associationClassResolver->resolveAssociationClass($entity);

                /** @var AssociationInterface $association */
                $association = new $associationClass();
                $association->setAssociationType($associationType);
                $entity->addAssociation($association);
            }
        }
    }
}
