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

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateRecordContext implements Context
{
    /** @var CreateRecordHandler */
    private $createRecordHandler;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ReferenceEntityRepositoryInterface  */
    private $referenceEntityRepository;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        CreateRecordHandler $createRecordHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->createRecordHandler = $createRecordHandler;
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @When /^the user creates a record "([^"]+)" for entity "([^"]+)" with:$/
     */
    public function theUserCreatesARecordWith(
        string $code,
        string $referenceEntityIdentifier,
        TableNode $updateTable
    ) {
        $updates = current($updateTable->getHash());
        $command = new CreateRecordCommand();
        $command->code = $code;
        $command->referenceEntityIdentifier = $referenceEntityIdentifier;
        $command->labels = json_decode($updates['labels'], true);

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createRecordHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a record with:$/
     */
    public function thereIsARecordWith(TableNode $referenceEntityTable)
    {
        $expectedInformation = current($referenceEntityTable->getHash());
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($expectedInformation['entity_identifier']);
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $actualRecord = $this->recordRepository->getByReferenceEntityAndCode(
            $referenceEntityIdentifier,
            RecordCode::fromString($expectedInformation['code'])
        );
        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualRecord,
            $attributeAsLabel
        );
    }

    private function assertSameLabels(array $expectedLabels, Record $record, AttributeAsLabelReference $attributeAsLabel)
    {
        $valueCollection = $record->getValues()->normalize();

        $actualLabels = $this->getLabelsFromValues($valueCollection, $attributeAsLabel->normalize());

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $actualLabels)
        );

        Assert::isEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    private function getLabelsFromValues(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    /**
     * @Given /^there should be no record$/
     */
    public function thereShouldBeNoRecord()
    {
        $referenceEntityCount = $this->recordRepository->count();
        Assert::same(
            0,
            $referenceEntityCount,
            sprintf('Expected to have 0 reference entity. %d found.', $referenceEntityCount)
        );
    }

    /**
     * @Given /^(\d+) random records for a reference entity$/
     */
    public function randomRecordsForAReferenceEntity(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $command = new CreateRecordCommand();
            $command->code = uniqid('record_');
            $command->referenceEntityIdentifier = 'designer';
            $command->labels = [];

            $this->violationsContext->addViolations($this->validator->validate($command));

            ($this->createRecordHandler)($command);
        }
    }
}
