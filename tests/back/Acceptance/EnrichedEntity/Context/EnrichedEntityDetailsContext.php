<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity\Context;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\FindEnrichedEntityQuery;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EnrichedEntityDetailsContext implements Context
{
    /** @var FindEnrichedEntityQuery */
    private $findEnrichedEntityQuery;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var string */
    private $expectedIdentifier;

    /** @var EnrichedEntityDetails */
    private $entityFound;

    /**
     * @param FindEnrichedEntityQuery  $findEnrichedEntityQuery
     * @param EnrichedEntityRepository $enrichedEntityRepository
     */
    public function __construct(
        FindEnrichedEntityQuery $findEnrichedEntityQuery,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->findEnrichedEntityQuery = $findEnrichedEntityQuery;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    /**
     * @Given /^the following enriched entities to show:$/
     */
    public function theFollowingEnrichedEntity(TableNode $enrichedEntityTable): void
    {
        foreach ($enrichedEntityTable->getHash() as $data) {
            $identifier = $data['identifier'];
            if ($identifier === null || $identifier === '') {
                continue;
            }
            $enrichedEntity = EnrichedEntity::create(
                EnrichedEntityIdentifier::fromString($identifier),
                json_decode($data['labels'], true)
            );
            $this->enrichedEntityRepository->add($enrichedEntity);
        }
    }

    /**
     * @When /^the user asks for the enriched entity "([^"]*)"$/
     *
     * @param string $identifier
     */
    public function theUserAskForTheEnrichedEntity(string $identifier): void
    {
        $this->entityFound = ($this->findEnrichedEntityQuery)($identifier);
    }

    /**
     * @Given /^the user gets the enriched entity "([^"]*)" with label "([^"]*)"$/
     *
     * @param string $expectedIdentifier
     * @param string $expectedLabel
     */
    public function iGetTheEnrichedEntityWithLabel(string $expectedIdentifier, string $expectedLabel): void
    {
        $this->expectedIdentifier = $expectedIdentifier;

        Assert::assertEquals($expectedIdentifier, $this->entityFound->identifier);
        $this->assertLabel($this->entityFound, $expectedLabel);
    }

    /**
     * @Then /^there is no enriched entity found for this identifier$/
     */
    public function thereIsNoEnrichedEntityFoundForThisIdentifier(): void
    {
        $errorMessage = sprintf('An entity with identifier "%s" was found', $this->expectedIdentifier);
        Assert::assertNull($this->entityFound, $errorMessage);
    }

    /**
     * @param EnrichedEntityDetails $enrichedEntityDetails
     * @param string                $expectedLabel
     *
     * @throws \LogicException
     */
    private function assertLabel(EnrichedEntityDetails $enrichedEntityDetails, string $expectedLabel): void
    {
        foreach ($enrichedEntityDetails->labels as $locale => $label) {
            if ($label === $expectedLabel) {
                return;
            }
        }

        throw new \LogicException(
            sprintf(
                'Expected enriched entity "%s" to have a label "%s", but it was not found.',
                $enrichedEntityDetails->identifier,
                $expectedLabel
            )
        );
    }
}
