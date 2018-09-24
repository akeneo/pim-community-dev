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

use Akeneo\EnrichedEntity\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\EnrichedEntity\Acceptance\Context\ExceptionContext;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommandFactory;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\EnrichedEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\EnrichedEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\FileData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditRecordContext implements Context
{
    private const ENRICHED_ENTITY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const RECORD_CODE = 'stark';
    private const TEXT_ATTRIBUTE_CODE = 'name';
    private const TEXT_ATTRIBUTE_IDENTIFIER = 'name_designer_fingerprint';
    private const IMAGE_ATTRIBUTE_CODE = 'primary_picture';
    private const IMAGE_ATTRIBUTE_IDENTIFIER = 'primary_picture_designer_fingerprint';

    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var EditRecordCommandFactory */
    private $editRecordCommandFactory;

    /** @var EditRecordHandler */
    private $editRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^the following records for the enriched entity "(.+)":$/
     */
    public function theFollowingRecords(string $entityIdentifier, TableNode $recordsTable)
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($entityIdentifier);

        foreach ($recordsTable->getHash() as $record) {
            $values = isset($record['values']) ? json_decode($record['values'], true) : [];
            var_dump($values);
            $this->recordRepository->create(Record::create(
                RecordIdentifier::fromString($record['identifier']),
                $enrichedEntityIdentifier,
                RecordCode::fromString($record['code']),
                json_decode($record['labels'], true),
                ValueCollection::fromValues($values)
            ));
        }
    }

    /**
     * @When /^the user updates the values of record "([^"]+)" with:$/
     */
    public function theUserUpdatesTheRecordValuesWith(string $identifier, TableNode $updateTable)
    {
        $actualRecord = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));

        $command = new EditRecordCommand();
        $command->identifier = $identifier;
        $command->enrichedEntityIdentifier = (string) $actualRecord->getEnrichedEntityIdentifier();
        $command->labels = [];

        $values = [];
        foreach ($updateTable->getHash() as $rowValues) {
            $values[] = json_decode($rowValues['data'], true);
        }
        $command->values = $values;
        ($this->editRecordHandler)($command);
    }

    /**
     * @When /^the user updates the record "([^"]+)" with:$/
     */
    public function theUserUpdatesTheRecordWith(string $identifier, TableNode $updateTable)
    {
        $actualRecord = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));

        $command = new EditRecordCommand();
        $command->identifier = $identifier;
        $command->enrichedEntityIdentifier = (string) $actualRecord->getEnrichedEntityIdentifier();

        $updates = $updateTable->getRowsHash();
        $command->labels = isset($updates['labels']) ? json_decode($updates['labels'], true) : [];
        $command->values = isset($updates['values']) ? json_decode($updates['values'], true) : [];

        ($this->editRecordHandler)($command);
    }

    /**
     * @Then /^the record "([^"]+)" should be:$/
     */
    public function theRecordShouldBe(string $identifier, TableNode $enrichedEntityTable)
    {
        $expectedIdentifier = RecordIdentifier::fromString($identifier);
        $expectedInformation = current($enrichedEntityTable->getHash());
        $actualRecord = $this->recordRepository->getByIdentifier($expectedIdentifier);

        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualRecord
        );
    }

    /**
     * @Then /^the values of record "([^"]+)" should be:$/
     */
    public function theRecordValuesShouldBe(string $identifier, TableNode $updateTable)
    {
        $expectedIdentifier = RecordIdentifier::fromString($identifier);
        $actualRecord = $this->recordRepository->getByIdentifier($expectedIdentifier);

        $expectedValues = [];
        foreach ($updateTable->getHash() as $value) {
            $expectedValues[] = json_decode($value['data'], true);
        }

        $notFound = [];
        foreach ($expectedValues as $expectedValue) {
            if (!$this->recordHasValue($expectedValue, $actualRecord)) {
                $notFound[] = $expectedValue;
            }
        }

        Assert::isEmpty(
            $notFound,
            sprintf('Expected values "%s" not found', json_encode($notFound))
        );
    }

    private function assertSameLabels(array $expectedLabels, Record $actualRecord)
    {
        $actualLabels = [];
        foreach ($actualRecord->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualRecord->getLabel($labelCode);
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

    private function recordHasValue(array $expectedValue, Record $actualRecord): bool
    {
        $actualValues = $actualRecord->getValues()->normalize();

        foreach ($actualValues as $actualValue) {
            $differences = array_diff($actualValue, $expectedValue);
            if (empty($differences)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Given /^an enriched entity with a text attribute$/
     * @throws \Exception
     */
    public function anEnrichedEntityWithATextAttribute()
    {
        $this->createEnrichedEntity();
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ENRICHED_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^a record belonging to this enriched entity with a value of "([^"]*)" for the text attribute$/
     */
    public function aRecordBelongingToThisEnrichedEntityWithAValueOfFor(string $textData)
    {
        $textValue = Value::create(
            AttributeIdentifier::create(
                self::ENRICHED_ENTITY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString($textData)
        );
        $this->createRecord($textValue);
    }

    /**
     * @When /^the user updates the text attribute of the record to "([^"]*)"$/
     */
    public function theUserUpdatesTheTextOfOfTheRecordTo(string $newData): void
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'enriched_entity_identifier' => self::ENRICHED_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $newData,
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Then /^the record should have the text value "([^"]*)" for this attribute$/
     */
    public function theRecordShouldHaveTheTextValueFor(string $expectedValue): void
    {
        $record = $this->recordRepository->getByEnrichedEntityAndCode(
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::ENRICHED_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($value);
        Assert::assertEquals($expectedValue, $value->getData()->normalize());
    }

    /**
     * @Given /^an enriched entity with an image attribute$/
     */
    public function anEnrichedEntityWithAImageAttribute()
    {
        $this->createEnrichedEntity();
        $this->createAttributeImage();
    }

    /**
     * @Given /^a record belonging to this enriched entity with a the file "([^"]*)" for the image attribute$/
     */
    public function aRecordBelongingToThisEnrichedEntityWithATheFileForTheImageAttribute(string $originalFilename)
    {
        $file = new FileInfo();
        $file->setOriginalFilename($originalFilename);
        $file->setKey('/a/dummy/key');

        $fileValue = Value::create(
            AttributeIdentifier::create(
                self::ENRICHED_ENTITY_IDENTIFIER,
                self::IMAGE_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($file)
        );
        $this->createRecord($fileValue);
    }

    /**
     * @When /^the user updates the image attribute of the record to "([^"]*)"$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordTo(string $originalFileName)
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'enriched_entity_identifier' => self::ENRICHED_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => $originalFileName,
                        'filePath'         => '/tmp/' . $originalFileName,
                    ],
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the record should have the image "([^"]*)" for this attribute$/
     */
    public function theRecordShouldHaveTheImageForThisAttribute(string $expectedOriginalFilename)
    {
        $record = $this->recordRepository->getByEnrichedEntityAndCode(
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::ENRICHED_ENTITY_IDENTIFIER, self::IMAGE_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($value);
        $normalizeData = $value->getData()->normalize();
        Assert::assertArrayHasKey('original_filename', $normalizeData);
        Assert::assertEquals($expectedOriginalFilename, $normalizeData['original_filename']);
    }

    /**
     * @Then /^there should be a validation error on the property text attribute with message "([^\']*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyTextAttributeWithMessage(string $expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage('values.' . self::TEXT_ATTRIBUTE_CODE, $expectedMessage);
    }

    /**
     * @Given /^an enriched entity with a text attribute with max length (\d+)$/
     * @throws \Exception
     */
    public function anEnrichedEntityWithATextAttributeWithMaxLength(int $maxLength)
    {
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ENRICHED_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger($maxLength),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    private function createEnrichedEntity(): void
    {
        $this->enrichedEntityRepository->create(EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            [],
            null
        ));
    }

    private function createAttributeImage(): void
    {
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::ENRICHED_ENTITY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('150.110'),
                AttributeAllowedExtensions::fromList(['jpeg', 'png'])
            )
        );
    }

    private function createRecord(Value $value): void
    {
        $this->recordRepository->create(
            Record::create(
                RecordIdentifier::create(self::ENRICHED_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE),
                [],
                ValueCollection::fromValues([$value])
            )
        );
    }

    private function executeCommand(EditRecordCommand $editCommand): void
    {
        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);
        }

        try {
            ($this->editRecordHandler)($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
