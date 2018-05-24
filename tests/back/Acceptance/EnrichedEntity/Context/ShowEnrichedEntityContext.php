<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity\Context;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show\ShowEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowEnrichedEntityContext implements Context
{
    /** @var ShowEnrichedEntityHandler */
    private $showEnrichedEntityHandler;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var EnrichedEntity[] */
    private $entitiesFound;

    /**
     * @param ShowEnrichedEntityHandler $showEnrichedEntityHandler
     * @param EnrichedEntityRepository  $enrichedEntityRepository
     */
    public function __construct(
        ShowEnrichedEntityHandler $showEnrichedEntityHandler,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->showEnrichedEntityHandler = $showEnrichedEntityHandler;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    /**
     * @Given /^the following enriched entity:$/
     */
    public function theFollowingEnrichedEntity(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $identifier = $data['identifier'];
            if ($identifier === null || $identifier === '') {
                continue;
            }
            $enrichedEntity = EnrichedEntity::define(
                EnrichedEntityIdentifier::fromString($identifier),
                LabelCollection::fromArray([])
            );
            $this->enrichedEntityRepository->add($enrichedEntity);
        }
    }

    /**
     * @Then /^there should be no enriched entity$/
     */
    public function thereShouldBeNoEnrichedEntity()
    {
        $entitiesFoundCount = \count($this->entitiesFound);
        if ($entitiesFoundCount > 0) {
            throw new \LogicException(
                sprintf('There should be no entity found, "%d" found', $entitiesFoundCount)
            );
        }
    }

    /**
     * @Given /^the following enriched entities:$/
     */
    public function theFollowingEnrichedEntities(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $identifier = $data['identifier'];
            if ($identifier === null || $identifier === '') {
                continue;
            }
            $enrichedEntity = EnrichedEntity::define(
                EnrichedEntityIdentifier::fromString($identifier),
                LabelCollection::fromArray([])
            );
            $this->enrichedEntityRepository->add($enrichedEntity);
        }
    }

    /**
     * @When /^the user ask for the enriched entity list$/
     */
    public function theUserAskForTheEnrichedEntityList()
    {
        $this->entitiesFound = $this->showEnrichedEntityHandler->findAll();
    }

    /**
     * @Then /^the user should get a selection of (\d+) items out of (\d+) items in total$/
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
     * @Then /^I should get an enriched entity "([^"]*)"$/
     */
    public function iShouldGetAnEnrichedEntity(string $enrichedEntityIdentifier)
    {
        foreach ($this->entitiesFound as $enrichedEntity) {
            $isFound = $enrichedEntity->getIdentifier()->equals(
                EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier)
            );
            if ($isFound) {
                return;
            }
        }

        throw new \LogicException(
            sprintf('Expected enriched entity with id "%s" to be found, none given.', $enrichedEntityIdentifier)
        );
    }
}
