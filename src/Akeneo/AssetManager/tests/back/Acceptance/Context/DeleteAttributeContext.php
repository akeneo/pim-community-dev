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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Behat\Behat\Context\Context;

class DeleteAttributeContext implements Context
{
    private EditAttributeHandler $editAttributeHandler;

    private GetAttributeIdentifierInterface $getAttributeIdentifier;

    private DeleteAttributeHandler $deleteAttributeHandler;

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
     * @When /^the user deletes the attribute "(.+)" linked to the asset family "(.+)"$/
     */
    public function theUserDeletesTheAttribute(string $attributeCode, string $entityIdentifier)
    {
        $identifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($entityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $command = new DeleteAttributeCommand(
            (string) $identifier
        );
        ($this->deleteAttributeHandler)($command);
    }

    /**
     * @Then /^it is not possible to delete the attribute as label linked to this entity$/
     */
    public function itIsNotPossibleToDeleteTheAttributeAsLabelLinkedToThisEntity()
    {
        $identifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('label')
        );

        $command = new DeleteAttributeCommand(
            (string) $identifier
        );
        try {
            ($this->deleteAttributeHandler)($command);

            throw new \Exception('Attribute as label has been deleted but it should not.');
        } catch (\LogicException $e) {
        }
    }

    /**
     * @Then /^it is not possible to delete the attribute as main media linked to this entity$/
     */
    public function itIsNotPossibleToDeleteTheAttributeAsMainMediaLinkedToThisEntity()
    {
        $identifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $command = new DeleteAttributeCommand(
            (string) $identifier
        );
        try {
            ($this->deleteAttributeHandler)($command);

            throw new \Exception('Attribute as main media has been deleted but it should not.');
        } catch (\LogicException $e) {
        }
    }
}
