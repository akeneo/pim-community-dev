<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsAttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the attribute identifier given does not already exists
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIdentifierShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var ExistsAttributeInterface */
    private $existsAttribute;

    public function __construct(ExistsAttributeInterface $existsAttribute)
    {
        $this->existsAttribute = $existsAttribute;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $enrichedEntityIdentifier = $command->identifier['enriched_entity_identifier'];
        $identifier = $command->identifier['identifier'];
        $alreadyExists = $this->existsAttribute->withIdentifier(
            AttributeIdentifier::create(
                $enrichedEntityIdentifier,
                $identifier
            )
        );

        if ($alreadyExists) {
            $this->context->buildViolation(AttributeIdentifierShouldBeUnique::ERROR_MESSAGE)
                ->setParameter('%enriched_entity_identifier%', $enrichedEntityIdentifier)
                ->setParameter('%code%', $identifier)
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractCreateAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                AbstractCreateAttributeCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeIdentifierShouldBeUnique) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
