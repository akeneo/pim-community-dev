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

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateReferenceEntityContext implements Context
{
    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        ExceptionContext $exceptionContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @When /^the user creates an enriched entity "([^"]+)" with:$/
     */
    public function theUserCreatesAnReferenceEntityWith($code, TableNode $updateTable)
    {
        $updates = current($updateTable->getHash());
        $command = new CreateReferenceEntityCommand();
        $command->code = $code;
        $command->labels = json_decode($updates['labels'], true);
        try {
            ($this->createReferenceEntityHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an enriched entity "([^"]+)" with:$/
     */
    public function thereIsAnReferenceEntityWith(string $code, TableNode $referenceEntityTable)
    {
        $expectedIdentifier = ReferenceEntityIdentifier::fromString($code);
        $expectedInformation = current($referenceEntityTable->getHash());
        $actualReferenceEntity = $this->referenceEntityRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualReferenceEntity
        );
    }

    private function assertSameLabels(array $expectedLabels, ReferenceEntity $actualReferenceEntity)
    {
        $actualLabels = [];
        foreach ($actualReferenceEntity->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualReferenceEntity->getLabel($labelCode);
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
    public function thereShouldBeNoReferenceEntity()
    {
        $referenceEntityCount = $this->referenceEntityRepository->count();
        Assert::same(
            0,
            $referenceEntityCount,
            sprintf('Expected to have 0 enriched entity. %d found.', $referenceEntityCount)
        );
    }
}
