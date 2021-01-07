<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validate that the due date is not in the past for a creation.
 * There is not validation for update
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectDueDateValidator extends ConstraintValidator
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($project, Constraint $constraint)
    {
        if (!$project instanceof ProjectInterface) {
            throw new UnexpectedTypeException($constraint, ProjectInterface::class);
        }

        if (!$constraint instanceof ProjectDueDate) {
            throw new UnexpectedTypeException($constraint, ProjectDueDate::class);
        }

        if (null !== $project->getId()) {
            return;
        }

        $dueDate = $project->getDueDate();

        if (!$dueDate instanceof \DateTime) {
            return;
        }

        $dueDate->setTime(0, 0);
        $today = new \DateTime('now');
        $today->setTime(0, 0);

        $interval = $today->diff($dueDate);
        if (0 > (int) $interval->format('%r%a')) {
            $message = $this->translator->trans($constraint->message);
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
