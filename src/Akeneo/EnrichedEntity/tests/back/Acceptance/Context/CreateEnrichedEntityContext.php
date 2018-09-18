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

use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Common\Fake\InMemoryEnrichedEntityRepository;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateEnrichedEntityContext implements Context
{
    /** @var InMemoryEnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var CreateEnrichedEntityHandler */
    private $createEnrichedEntityHandler;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        CreateEnrichedEntityHandler $createEnrichedEntityHandler,
        ExceptionContext $exceptionContext
    ) {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->createEnrichedEntityHandler = $createEnrichedEntityHandler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @When /^the user creates an enriched entity "([^"]+)" with:$/
     */
    public function theUserCreatesAnEnrichedEntityWith($code, TableNode $updateTable)
    {
        $updates = current($updateTable->getHash());
        $command = new CreateEnrichedEntityCommand();
        $command->code = $code;
        $command->labels = json_decode($updates['labels'], true);
        try {
            ($this->createEnrichedEntityHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an enriched entity "([^"]+)" with:$/
     */
    public function thereIsAnEnrichedEntityWith(string $code, TableNode $enrichedEntityTable)
    {
        $expectedIdentifier = EnrichedEntityIdentifier::fromString($code);
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
     * @Given /^there should be no enriched entity$/
     */
    public function thereShouldBeNoEnrichedEntity()
    {
        $enrichedEntityCount = $this->enrichedEntityRepository->count();
        Assert::same(
            0,
            $enrichedEntityCount,
            sprintf('Expected to have 0 enriched entity. %d found.', $enrichedEntityCount)
        );
    }
}
