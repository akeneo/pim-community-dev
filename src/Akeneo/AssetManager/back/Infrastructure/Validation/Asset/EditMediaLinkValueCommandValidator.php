<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditMediaLinkValueCommand as EditMediaLinkValueCommandConstraint;
use Akeneo\AssetManager\Infrastructure\Validation\Attribute\MediaLink\Prefix;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class EditMediaLinkValueCommandValidator extends ConstraintValidator
{
    const MEDIA_TYPE_WITH_URL = [
        MediaType::IMAGE,
        MediaType::PDF,
    ];

    /** @var string[] */
    private array $allowedProtocols;

    public function __construct(array $allowedProtocols)
    {
        $this->allowedProtocols = $allowedProtocols;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command, $command->attribute);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof EditMediaLinkValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditMediaLinkValueCommand::class,
                    get_class($command)
                )
            );
        }

        if (!$command->attribute instanceof MediaLinkAttribute) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditMediaLinkValueCommand::class,
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
        if (!$constraint instanceof EditMediaLinkValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditMediaLinkValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditMediaLinkValueCommand $command, MediaLinkAttribute $attribute): void
    {
        if (!in_array($attribute->getMediaType()->normalize(), self::MEDIA_TYPE_WITH_URL)) {
            return;
        }

        $url = $attribute->getPrefix()->stringValue() . $command->mediaLink;
        if (!$this->mediaLinkContainAnAllowedProtocol($url)) {
            $this->context->buildViolation(EditMediaLinkValueCommandConstraint::PROTOCOL_NOT_ALLOWED)
                ->setParameter('%allowed_protocols%', implode(', ', $this->allowedProtocols))
                ->atPath((string) $command->attribute->getCode())
                ->addViolation();
        }
    }

    private function mediaLinkContainAnAllowedProtocol(string $url): bool
    {
        foreach ($this->allowedProtocols as $allowedProtocol) {
            if (str_starts_with($url, $allowedProtocol)) {
                return true;
            }
        }

        return false;
    }
}
