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
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validate the project's locale, it must belong to the project's channel
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectLocaleValidator extends ConstraintValidator
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

        if (!$constraint instanceof ProjectLocale) {
            throw new UnexpectedTypeException($constraint, ProjectLocale::class);
        }

        $locale = $project->getLocale();
        $channel = $project->getChannel();
        if (null !== $locale && !$locale->hasChannel($channel)) {
            $message = $this->translator->trans($constraint->message, ['{{ locale }}' => $locale->getCode(), '{{ channel }}' => $channel->getCode()]);

            $this->context->buildViolation($message)->atPath('locale')->addViolation();
        }
    }
}
