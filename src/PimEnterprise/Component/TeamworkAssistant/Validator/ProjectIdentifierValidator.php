<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Validator;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;
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

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param TranslatorInterface                   $translator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $projectRepository,
        TranslatorInterface $translator
    ) {
        $this->projectRepository = $projectRepository;
        $this->translator = $translator;
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
            $message = $this->translator->trans($constraint->message, ['{{ project }}' => $projectIdentifier]);

            $this->context->buildViolation($message)->addViolation();
        }
    }
}
