<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Doctrine\ORM\EntityRepository;
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

    public function __construct(DatagridViewRepositoryInterface $datagridViewRepository)
    {
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

        $datagridViewFromDb = null;

        // Pull-up master/6.0: remove the first if to keep only that is inside. The interface now has the methods so
        //  no need to test that.
        if (\method_exists($this->datagridViewRepository, 'findPublicDatagridViewByLabel')
            && \method_exists($this->datagridViewRepository, 'findPrivateDatagridViewByLabel')) {
            if (DatagridView::TYPE_PUBLIC === $entity->getType()) {
                $datagridViewFromDb = $this->datagridViewRepository->findPublicDatagridViewByLabel($entity->getLabel());
            } elseif (DatagridView::TYPE_PRIVATE === $entity->getType()) {
                $datagridViewFromDb = $this->datagridViewRepository->findPrivateDatagridViewByLabel($entity->getLabel(),
                    $entity->getOwner());
            }
        } elseif ($this->datagridViewRepository instanceof EntityRepository) {
            if (DatagridView::TYPE_PUBLIC === $entity->getType()) {
                $datagridViewFromDb = $this->datagridViewRepository->findOneBy([
                    'label' => $entity->getLabel(),
                    'type' => DatagridView::TYPE_PUBLIC,
                ]);
            } else {
                $datagridViewFromDb = $this->datagridViewRepository->findOneBy([
                    'label' => $entity->getLabel(),
                    'owner' => $entity->getOwner(),
                    'type' => DatagridView::TYPE_PRIVATE,
                ]);
            }
        }

        if (null !== $datagridViewFromDb
            && $datagridViewFromDb->getId() !== $entity->getId()
        ) {
            $this->context->buildViolation($constraint->message)
                ->atPath('label')
                ->addViolation();
        }
    }
}
