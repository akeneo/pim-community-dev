<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\Security\Core\Security;
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
    private Security $security;

    public function __construct(
        DatagridViewRepositoryInterface $datagridViewRepository,
        Security $security
    ) {
        $this->datagridViewRepository = $datagridViewRepository;
        $this->security = $security;
    }

    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDatagridViewEntity) {
            throw new UnexpectedTypeException($constraint, UniqueDatagridViewEntity::class);
        }

        if (!$entity instanceof DatagridView) {
            throw new UnexpectedTypeException($constraint, DatagridView::class);
        }

        $isLabelUnique = false;

        if (DatagridView::TYPE_PUBLIC === $entity->getType()) {
            $isLabelUnique = $this->isPublicViewLabelUnique();
        } elseif(DatagridView::TYPE_PRIVATE === $entity->getType()) {
            $user = $this->security->getUser();
            $isLabelUnique = $this->isPrivateViewLabelUnique($user);
        }

        if (!$isLabelUnique) {
            $this->context->buildViolation($constraint->message)
                ->atPath('label')
                ->addViolation();
        }
    }

    private function isPublicViewLabelUnique(DatagridView $datagridView): bool
    {
        $label = $this->datagridViewRepository->searchPublicViewLabel($datagridView->getLabel());

        return $label === $datagridView->getLabel();
    }

    private function isPrivateViewLabelUnique(DatagridView $datagridView): bool
    {
        $this->datagridViewRepository->searchPrivateViewLabel($datagridView->getLabel());

        return false;
    }
}
