<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Validator;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectIdentifierValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($projectIdentifier, Constraint $constraint)
    {
        if (!$constraint instanceof ProjectIdentifier) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ProjectIdentifier');
        }

        if (null === $this->projectRepository->findOneByIdentifier($projectIdentifier)) {
            $this->context->buildViolation(sprintf($constraint->message, $projectIdentifier))
                ->addViolation();
        }
    }
}
