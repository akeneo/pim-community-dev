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
use Symfony\Component\Validator\Validator\ValidatorInterface;
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

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @When /^the user creates an reference entity "([^"]+)" with:$/
     */
    public function theUserCreatesAnReferenceEntityWith($code, TableNode $updateTable)
    {
        $updates = current($updateTable->getHash());
        $command = new CreateReferenceEntityCommand();
        $command->code = $code;
        $command->labels = json_decode($updates['labels'], true);

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createReferenceEntityHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an reference entity "([^"]+)" with:$/
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
     * @Given /^there should be no reference entity$/
     */
    public function thereShouldBeNoReferenceEntity()
    {
        $referenceEntityCount = $this->referenceEntityRepository->count();
        Assert::same(
            0,
            $referenceEntityCount,
            sprintf('Expected to have 0 reference entity. %d found.', $referenceEntityCount)
        );
    }

    /**
     * @Given /^(\d+) random reference entities$/
     */
    public function randomReferenceEntities(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $command = new CreateReferenceEntityCommand();
            $command->code = uniqid();
            $command->labels = ['en_US' => uniqid('label_')];

            if ($this->validator->validate($command)->count() > 0) {
                throw new \RuntimeException('Cannot create the reference entity, command not valid');
            }

            ($this->createReferenceEntityHandler)($command);
        }
    }
}
