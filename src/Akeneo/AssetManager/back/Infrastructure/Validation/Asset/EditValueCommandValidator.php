<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\IsNull;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
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

        $channelViolations = $this->checkChannelType($command);
        $this->addViolations($command, $channelViolations, '.channel');
        $localeViolations = $this->checkLocaleType($command);
        $this->addViolations($command, $localeViolations, '.locale');

        if (count($channelViolations) > 0 || count($localeViolations) > 0) {
            return;
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
            $constraintNotNull = new NotNull();
            $constraintNotNull->message = EditValueCommand::CHANNEL_IS_EXPECTED;
            $constraints = [ $constraintNotNull, new Type('string')];
        } else {
            $constraintNull = new IsNull();
            $constraintNull->message = EditValueCommand::CHANNEL_IS_NOT_EXPECTED;
            $constraints = [$constraintNull];
        }

        $validator = Validation::createValidator();

        return $validator->validate($command->channel, $constraints);
    }

    private function checkLocaleType(AbstractEditValueCommand $command): ConstraintViolationListInterface
    {
        if ($command->attribute->hasValuePerLocale()) {
            $constraintNotNull = new NotNull();
            $constraintNotNull->message = EditValueCommand::LOCALE_IS_EXPECTED;
            $constraints = [ $constraintNotNull, new Type('string')];
        } else {
            $constraintNull = new IsNull();
            $constraintNull->message = EditValueCommand::LOCALE_IS_NOT_EXPECTED;
            $constraints = [$constraintNull];
        }

        $validator = Validation::createValidator();

        return $validator->validate($command->locale, $constraints);
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

    private function addViolations(AbstractEditValueCommand $command, ConstraintViolationListInterface $violations, ?string $path = null): void
    {
        $attributeCode = (string) $command->attribute->getCode();
        foreach ($violations as $violation) {
            $this->context->buildViolation($violation->getMessage())
                ->setParameter('%attribute_code%', $attributeCode)
                ->atPath($attributeCode . $path)
                ->setCode($violation->getCode())
                ->setPlural($violation->getPlural())
                ->setInvalidValue($violation->getInvalidValue())
                ->addViolation();
        }
    }
}
