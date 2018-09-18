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

namespace Akeneo\EnrichedEntity\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditEnrichedEntityContext implements Context
{
    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    /** @var EditEnrichedEntityHandler */
    private $editEnrichedEntityHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationListInterface */
    private $violations;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /**
     * @param EnrichedEntityRepositoryInterface $enrichedEntityRepository
     * @param EditEnrichedEntityHandler         $editEnrichedEntityHandler
     * @param ValidatorInterface                $validator
     * @param ConstraintViolationsContext       $constraintViolationsContext
     */
    public function __construct(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        EditEnrichedEntityHandler $editEnrichedEntityHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->editEnrichedEntityHandler = $editEnrichedEntityHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @Given /^the following enriched entity:$/
     */
    public function theFollowingEnrichedEntity(TableNode $enrichedEntitieTable)
    {
        foreach ($enrichedEntitieTable->getHash() as $enrichedEntity) {
            $this->enrichedEntityRepository->create(
                EnrichedEntity::create(
                    EnrichedEntityIdentifier::fromString($enrichedEntity['identifier']),
                    json_decode($enrichedEntity['labels'], true),
                    null
                )
            );
        }
    }

    /**
     * @When /^the user updates the enriched entity "([^"]*)" with:$/
     */
    public function theUserUpdatesTheEnrichedEntityWith(string $identifier, TableNode $updateTable)
    {
        $updates = $updateTable->getRowsHash();
        $command = new EditEnrichedEntityCommand();
        $command->identifier = $identifier;
        $command->labels = json_decode($updates['labels'], true);
        ($this->editEnrichedEntityHandler)($command);
    }

    /**
     * @Then /^the enriched entity "([^"]*)" should be:$/
     */
    public function theEnrichedEntityShouldBe(string $identifier, TableNode $enrichedEntityTable)
    {
        $expectedIdentifier = EnrichedEntityIdentifier::fromString($identifier);
        $expectedInformation = current($enrichedEntityTable->getHash());
        $actualEnrichedEntity = $this->enrichedEntityRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualEnrichedEntity
        );
    }

    private function assertSameLabels(array $expectedLabels, EnrichedEntity $actualEnrichedEntity)
    {
        $actualLabels = [];
        foreach ($actualEnrichedEntity->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualEnrichedEntity->getLabel($labelCode);
        }

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $expectedLabels)
        );

        Assert::assertEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    /**
     * @Given /^the enriched entity \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theEnrichedEntityWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $this->enrichedEntityRepository->create(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString($identifier),
                [$localCode => $label],
                null
            )
        );
    }

    /**
     * @Given /^an image on an enriched entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function anImageOnAnEnrichedEntityWitPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $file = new FileInfo();
        $file->setKey($filePath);
        $file->setOriginalFilename($filename);

        $this->enrichedEntityRepository->create(
            EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString($identifier),
                [],
                Image::fromFileInfo($file)
            )
        );
    }

    /**
     * @When /^the user updates the image of the enriched entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheImageOfTheEnrichedEntityWithPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $editEnrichedEntityCommand = new EditEnrichedEntityCommand();
        $editEnrichedEntityCommand->identifier = $identifier;
        $editEnrichedEntityCommand->labels = [];
        $editEnrichedEntityCommand->image = [
            'filePath' => $filePath,
            'originalFilename' => $filename
        ];
        $this->editEnrichedEntity($editEnrichedEntityCommand);
    }

    /**
     * @When /^the user updates the enriched entity \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheEnrichedEntityWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $editEnrichedEntityCommand = new EditEnrichedEntityCommand();
        $editEnrichedEntityCommand->identifier = $identifier;
        $editEnrichedEntityCommand->labels[$localCode] = $label;
        $editEnrichedEntityCommand->image = null;
        $this->editEnrichedEntity($editEnrichedEntityCommand);
    }

    /**
     * @Then /^the image of the enriched entity \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theImageOfTheEnrichedEntityShouldBe(string $identifier, string $filePath)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $filePath = json_decode($filePath);

        $enrichedEntity = $this->enrichedEntityRepository
            ->getByIdentifier(EnrichedEntityIdentifier::fromString($identifier));

        Assert::assertEquals($enrichedEntity->getImage()->getKey(), $filePath);
    }
    
    private function editEnrichedEntity(EditEnrichedEntityCommand $editEnrichedEntityCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editEnrichedEntityCommand));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editEnrichedEntityHandler)($editEnrichedEntityCommand);
        }
    }
}
