<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity\Context;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
final class EnrichedEntityDetailsContext implements Context
{
    /** @var FindEnrichedEntityDetailsInterface */
    private $findEnrichedEntityDetailsQueryHandler;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var string */
    private $expectedIdentifier;

    /** @var EnrichedEntityDetails */
    private $entityFound;

    /**
     * @param FindEnrichedEntityDetailsInterface $findEnrichedEntityDetailsQuery
     * @param EnrichedEntityRepository           $enrichedEntityRepository
     */
    public function __construct(
        FindEnrichedEntityDetailsInterface $findEnrichedEntityDetailsQuery,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->findEnrichedEntityDetailsQueryHandler = $findEnrichedEntityDetailsQuery;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    /**
     * @Given /^the following enriched entities to show:$/
     *
     * @param TableNode $enrichedEntityTable
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
            $this->enrichedEntityRepository->save($enrichedEntity);
        }
    }

    /**
     * @When /^the user asks for the enriched entity "([^"]*)"$/
     *
     * @param string $identifier
     */
    public function theUserAskForTheEnrichedEntity(string $identifier): void
    {
        $this->entityFound = ($this->findEnrichedEntityDetailsQueryHandler)(EnrichedEntityIdentifier::fromString($identifier));
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
     * @param EnrichedEntityDetails $enrichedEntityDetails
     * @param string                $expectedLabel
     *
     * @throws \LogicException
     */
    private function assertLabel(EnrichedEntityDetails $enrichedEntityDetails, string $expectedLabel): void
    {
        $localeCodes = $enrichedEntityDetails->labels->getLocaleCodes();
        foreach ($localeCodes as $localeCode) {
            if ($expectedLabel === $enrichedEntityDetails->labels->getLabel($localeCode)) {
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
