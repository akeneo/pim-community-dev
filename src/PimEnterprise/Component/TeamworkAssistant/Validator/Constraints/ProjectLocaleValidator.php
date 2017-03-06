<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Validator\Constraints;

use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate the project's locale, it must belong to the project's channel
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectLocaleValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
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

        if (!$constraint instanceof ProjectLocale) {
            throw new UnexpectedTypeException($constraint, ProjectLocale::class);
        }

        $locale = $project->getLocale();
        if (null !== $locale && !$locale->hasChannel($project->getChannel())) {
            $message = $this->translator->trans($constraint->message, ['{{ locale }}' => $locale->getCode()]);

            $this->context->buildViolation($message)->addViolation();
        }
    }
}
