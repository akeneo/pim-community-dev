<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityItem;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityItemsInterface;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
final class EnrichedEntityGridContext implements Context
{
    /** @var FindEnrichedEntityItemsInterface */
    private $findEnrichedEntityItemsQuery;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var EnrichedEntityItem[] */
    private $entitiesFound;

    /**
     * @param FindEnrichedEntityItemsInterface $findEnrichedEntityItemsQuery
     * @param EnrichedEntityRepository  $enrichedEntityRepository
     */
    public function __construct(
        FindEnrichedEntityItemsInterface $findEnrichedEntityItemsQuery,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->findEnrichedEntityItemsQuery = $findEnrichedEntityItemsQuery;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    /**
     * @Given the following enriched entities to list:
     */
    public function theFollowingEnrichedEntity(TableNode $table): void
    {
        foreach ($table->getHash() as $data) {
            $identifier = $data['identifier'];
            if ($identifier === null || $identifier === '') {
                continue;
            }
            $enrichedEntity = EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString($identifier),
                []
            );
            $this->enrichedEntityRepository->save($enrichedEntity);
        }
    }

    /**
     * @Then /^there is no enriched entity$/
     */
    public function thereShouldBeNoEnrichedEntity(): void
    {
        $entitiesFoundCount = count($this->entitiesFound);
        if ($entitiesFoundCount > 0) {
            throw new \LogicException(
                sprintf('There should be no entity found, "%d" found', $entitiesFoundCount)
            );
        }
    }

    /**
     * @Given /^the following enriched entities:$/
     */
    public function theFollowingEnrichedEntities(TableNode $table): void
    {
        foreach ($table->getHash() as $data) {
            $identifier = $data['identifier'];
            if ($identifier === null || $identifier === '') {
                continue;
            }
            $enrichedEntity = EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString($identifier),
                []
            );
            $this->enrichedEntityRepository->save($enrichedEntity);
        }
    }

    /**
     * @When /^the user asks for the enriched entity list$/
     */
    public function theUserAskForTheEnrichedEntityList(): void
    {
        $this->entitiesFound = ($this->findEnrichedEntityItemsQuery)();
    }

    /**
     * @Then /^the user gets a selection of (\d+) items out of (\d+) items in total$/
     */
    public function theUserShouldGetASelectionOfItemsOutOfItemsInTotal(int $numberOfItems, $arg2)
    {
        $actualCount = \count($this->entitiesFound);
        if ($actualCount !== $numberOfItems) {
            throw new \LogicException(
                sprintf('Expected number of entities to be found "%d", "%d" given', $numberOfItems, $actualCount)
            );
        }
    }

    /**
     * @Then /^the user gets an enriched entity "([^"]*)"$/
     */
    public function iShouldGetAnEnrichedEntity(string $expectedEnrichedEntityIdentifier): void
    {
        foreach ($this->entitiesFound as $enrichedEntity) {
            $isFound = $expectedEnrichedEntityIdentifier === $enrichedEntity->identifier;
            if ($isFound) {
                return;
            }
        }

        throw new \LogicException(
            sprintf('Expected enriched entity with id "%s" to be found, none given.', $expectedEnrichedEntityIdentifier)
        );
    }
}
