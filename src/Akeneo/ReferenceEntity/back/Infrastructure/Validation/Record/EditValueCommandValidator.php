<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditValueCommandValidator extends ConstraintValidator
{
    private ChannelExistsInterface $channelExists;
    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;
    private FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers;

    public function __construct(
        ChannelExistsInterface $channelExists,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers
    ) {
        $this->channelExists = $channelExists;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkCommandType($command);
        $this->checkConstraintType($constraint);

        $attribute = $command->attribute;
        if ($attribute->hasValuePerChannel() && $attribute->hasValuePerLocale()) {
            if (null === $command->locale || null === $command->channel) {
                $attributeCode = (string) $command->attribute->getCode();
                $referenceEntityIdentifier = (string) $command->attribute->getReferenceEntityIdentifier();
                $this->context->buildViolation(EditValueCommand::CHANNEL_AND_LOCALE_ARE_EXPECTED)
                    ->setParameter('%attribute_code%', $attributeCode)
                    ->setParameter('%reference_entity_identifier%', $referenceEntityIdentifier)
                    ->atPath($attributeCode)
                    ->setInvalidValue($command)
                    ->addViolation();

                return;
            }
        } else {
            $violations = new ConstraintViolationList();
            $violations->addAll($this->checkChannelType($command));
            $violations->addAll($this->checkLocaleType($command));
            if ($violations->count() > 0) {
                $this->addViolations($command, $violations);

                return;
            }
        }

        if ($command->attribute->hasValuePerChannel()) {
            $this->checkScopableValue($command);
        } elseif ($command->attribute->hasValuePerLocale()) {
            $this->checkLocalizableValue($command);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractEditValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditValueCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditValueCommand) {
            throw new UnexpectedTypeException($constraint, EditValueCommand::class);
        }
    }

    private function checkChannelType(AbstractEditValueCommand $command): ConstraintViolationListInterface
    {
        if ($command->attribute->hasValuePerChannel()) {
            $constraintNotNull = new Constraints\NotNull();
            $constraintNotNull->message = EditValueCommand::CHANNEL_IS_EXPECTED;
            $constraints = [ $constraintNotNull, new Constraints\Type('string')];
        } else {
            $constraintNull = new Constraints\IsNull();
            $constraintNull->message = EditValueCommand::CHANNEL_IS_NOT_EXPECTED;
            $constraints = [$constraintNull];
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($command->channel, $constraints);

        return $violations;
    }

    private function checkLocaleType(AbstractEditValueCommand $command): ConstraintViolationListInterface
    {
        if ($command->attribute->hasValuePerLocale()) {
            $constraintNotNull = new Constraints\NotNull();
            $constraintNotNull->message = EditValueCommand::LOCALE_IS_EXPECTED;
            $constraints = [ $constraintNotNull, new Constraints\Type('string')];
        } else {
            $constraintNull = new Constraints\IsNull();
            $constraintNull->message = EditValueCommand::LOCALE_IS_NOT_EXPECTED;
            $constraints = [$constraintNull];
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($command->locale, $constraints);

        return $violations;
    }

    private function checkScopableValue(AbstractEditValueCommand $command): void
    {
        if (!$this->channelExists->exists(ChannelIdentifier::fromCode($command->channel))) {
            $this->context->buildViolation(EditValueCommand::CHANNEL_SHOULD_EXIST)
                ->setParameter('%channel_identifier%', $command->channel)
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();

            return;
        }

        if ($command->attribute->hasValuePerLocale()) {
            $this->checkLocaleIsActivatedForChannel($command);
        }
    }

    private function checkLocalizableValue(AbstractEditValueCommand $command): void
    {
        $activatedLocales = $this->findActivatedLocalesByIdentifiers->find(LocaleIdentifierCollection::fromNormalized([$command->locale]));

        if ($activatedLocales->isEmpty()) {
            $this->context->buildViolation(EditValueCommand::LOCALE_IS_NOT_ACTIVATED)
                ->setParameter('%locale_identifier%', $command->locale)
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();
        }
    }

    private function checkLocaleIsActivatedForChannel(AbstractEditValueCommand $command): void
    {
        $activatedLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();

        if (!array_key_exists($command->channel, $activatedLocalesPerChannels)
            || !in_array($command->locale, $activatedLocalesPerChannels[$command->channel])
        ) {
            $this->context->buildViolation(EditValueCommand::LOCALE_IS_NOT_ACTIVATED_FOR_CHANNEL)
                ->setParameter('%locale_identifier%', $command->locale)
                ->setParameter('%channel_identifier%', $command->channel)
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();
        }
    }

    private function addViolations(AbstractEditValueCommand $command, ConstraintViolationListInterface $violations): void
    {
        $attributeCode = (string) $command->attribute->getCode();
        $referenceEntityIdentifier = (string) $command->attribute->getReferenceEntityIdentifier();
        foreach ($violations as $violation) {
            $this->context->buildViolation($violation->getMessage())
                ->setParameter('%attribute_code%', $attributeCode)
                ->setParameter('%reference_entity_identifier%', $referenceEntityIdentifier)
                ->atPath($attributeCode)
                ->setCode($violation->getCode())
                ->setPlural($violation->getPlural())
                ->setInvalidValue($violation->getInvalidValue())
                ->addViolation();
        }
    }
}
