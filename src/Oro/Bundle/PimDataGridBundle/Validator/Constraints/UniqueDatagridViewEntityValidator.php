<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueDatagridViewEntityValidator extends ConstraintValidator
{
    private DatagridViewRepositoryInterface $datagridViewRepository;

    public function __construct(
        DatagridViewRepositoryInterface $datagridViewRepository
    ) {
        $this->datagridViewRepository = $datagridViewRepository;
    }

    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDatagridViewEntity) {
            throw new UnexpectedTypeException($constraint, UniqueDatagridViewEntity::class);
        }

        if (!$entity instanceof DatagridView) {
            throw new UnexpectedTypeException($constraint, DatagridView::class);
        }

        $isLabelUnique = true;
        $datagridViewFromDb = null;

        if (DatagridView::TYPE_PUBLIC === $entity->getType()) {
            $datagridViewFromDb = $this->datagridViewRepository->findPublicDatagridViewByLabel($entity->getLabel());
        } elseif(DatagridView::TYPE_PRIVATE === $entity->getType()) {
            $datagridViewFromDb = $this->datagridViewRepository->findPrivateDatagridViewByLabel($entity->getLabel(), $entity->getOwner());
        }

        if (null !== $datagridViewFromDb
            && $datagridViewFromDb->getId() !== $entity->getId()
        ) {
            $isLabelUnique = false;
        }

        if (!$isLabelUnique) {
            $this->context->buildViolation($constraint->message)
                ->atPath('label')
                ->addViolation();
        }
    }
}
