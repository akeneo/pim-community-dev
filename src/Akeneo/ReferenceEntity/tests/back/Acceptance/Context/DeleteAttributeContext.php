<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Behat\Behat\Context\Context;

class DeleteAttributeContext implements Context
{
    /** @var EditAttributeHandler */
    private $editAttributeHandler;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var DeleteAttributeHandler */
    private $deleteAttributeHandler;

    public function __construct(
        EditAttributeHandler $editAttributeHandler,
        DeleteAttributeHandler $deleteAttributeHandler,
        GetAttributeIdentifierInterface $getAttributeIdentifier
    ) {
        $this->editAttributeHandler = $editAttributeHandler;
        $this->deleteAttributeHandler = $deleteAttributeHandler;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
    }

    /**
     * @When /^the user deletes the attribute "(.+)" linked to the reference entity "(.+)"$/
     */
    public function theUserDeletesTheAttribute(string $attributeCode, string $entityIdentifier)
    {
        $identifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString($entityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $command = new DeleteAttributeCommand();
        $command->attributeIdentifier = (string) $identifier;
        ($this->deleteAttributeHandler)($command);
    }

    /**
     * @Then /^it is not possible to delete the attribute as label linked to this entity$/
     */
    public function itIsNotPossibleToDeleteTheAttributeAsLabelLinkedToThisEntity()
    {
        $identifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('label')
        );

        $command = new DeleteAttributeCommand();
        $command->attributeIdentifier = (string) $identifier;
        try {
            ($this->deleteAttributeHandler)($command);

            throw new \Exception('Attribute as label has been deleted but it should not.');
        } catch (\LogicException $e) {
        }
    }

    /**
     * @Then /^it is not possible to delete the attribute as image linked to this entity$/
     */
    public function itIsNotPossibleToDeleteTheAttributeAsImageLinkedToThisEntity()
    {
        $identifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image')
        );

        $command = new DeleteAttributeCommand();
        $command->attributeIdentifier = (string) $identifier;
        try {
            ($this->deleteAttributeHandler)($command);

            throw new \Exception('Attribute as image has been deleted but it should not.');
        } catch (\LogicException $e) {
        }
    }
}
