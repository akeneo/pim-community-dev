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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
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
                    json_decode($enrichedEntity['labels'], true)
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

        Assert::isEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    /**
     * @When /^the user updates the \'([^\']*)\' enriched entity image with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheEnrichedEntityImageWithPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $enrichedEntity = $this->enrichedEntityRepository
            ->getByIdentifier(EnrichedEntityIdentifier::fromString($identifier));

        $editImage = new EditEnrichedEntityCommand();
        $editImage->identifier = $identifier;
        foreach ($enrichedEntity->getLabelCodes() as $localCode) {
            $editImage->labels[$localCode] = $enrichedEntity->getLabel($localCode);
        }
        $editImage->image = [
            'filePath' => $filePath,
            'originalFilename' => $filename
        ];
        $this->constraintViolationsContext->addViolations($this->validator->validate($editImage));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editEnrichedEntityHandler)($editImage);
        }
    }

    /**
     * @Then /^the image of the \'([^\']*)\' enriched entity should be \'([^\']*)\'$/
     */
    public function theImageOfTheEnrichedEntityShouldBe(string $identifier, string $filePath)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $filePath = json_decode($filePath);

        $enrichedEntity = $this->enrichedEntityRepository
            ->getByIdentifier(EnrichedEntityIdentifier::fromString($identifier));

        Assert::assertEquals($enrichedEntity->getImage()->getKey(), $filePath);
    }
}
