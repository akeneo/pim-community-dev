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

    /**
     * @param EnrichedEntityRepositoryInterface $enrichedEntityRepository
     * @param EditEnrichedEntityHandler         $editEnrichedEntityHandler
     */
    public function __construct(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        EditEnrichedEntityHandler $editEnrichedEntityHandler
    ) {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->editEnrichedEntityHandler = $editEnrichedEntityHandler;
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
     * @When /^the user updates the \'([^\']*)\' enriched entity image with \'([^\']*)\'$/
     */
    public function theUserUpdatesTheEnrichedEntityWithOn(string $identifier, string $filePath): void
    {
        $identifier = json_decode($identifier);
        $filePath = json_decode($filePath);

        $enrichedEntity = $this->enrichedEntityRepository
            ->getByIdentifier(EnrichedEntityIdentifier::fromString($identifier));

        $editImage = new EditEnrichedEntityCommand();
        $editImage->identifier = $identifier;
        foreach ($enrichedEntity->getLabelCodes() as $localCode) {
            $editImage->labels[$localCode] = $enrichedEntity->getLabel($localCode);
        }
        $editImage->image = [
            'filePath' => $filePath,
            'originalFilename' => basename($filePath)
        ];

        ($this->editEnrichedEntityHandler)($editImage);
    }

    /**
     * @Then /^the image of the \'([^\']*)\' enriched entity should be \'([^\']*)\'$/
     */
    public function theOfTheEnrichedEntityShouldBe(string $identifier, string $filePath)
    {
        $identifier = json_decode($identifier);
        $filePath = json_decode($filePath);

        $enrichedEntity = $this->enrichedEntityRepository
            ->getByIdentifier(EnrichedEntityIdentifier::fromString($identifier));

        Assert::assertEquals($enrichedEntity->getImage(), $filePath);
    }
}
