<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context\EditRecord;

use Akeneo\ReferenceEntity\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\ReferenceEntity\Acceptance\Context\ExceptionContext;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\PreviewType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\UrlData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditUrlContext implements Context
{
    private const REFERENCE_ENTITY_IDENTIFIER = 'designer';
    private const ATTRIBUTE_CODE = 'website';
    private const ATTRIBUTE_IDENTIFIER = 'website_designer_fingerprint';
    private const RECORD_CODE = 'stark';
    private const FINGERPRINT = 'fingerprint';
    private const NEW_URL = 'house_2345112';
    private const OLD_URL = 'garden_5124';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

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
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^a reference entity with an url attribute and a record belonging to this reference entity$/
     */
    public function aReferenceEntityWithAnUrlAttributeAndARecordBelongingToThisReferenceEntity(): void
    {
        $this->createReferenceEntity();
        $this->createUrlAttribute();
        $this->createRecord();
    }

    /**
     * @When /^the user updates the url value of the record$/
     */
    public function theUserUpdatesTheUrlValueOfTheRecord(): void
    {
        $editCommand = $this->editRecordCommandFactory->create(
            [
                'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
                'code'                        => self::RECORD_CODE,
                'labels'                      => [],
                'values'                      => [
                    [
                        'attribute' => self::ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => self::NEW_URL,
                    ],
                ],
            ]
        );

        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->editRecordHandler)($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Given /^the record should have the url value for this attribute$/
     */
    public function theRecordShouldHaveTheUrlValueForThisAttribute(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($value);
        Assert::assertSame(self::NEW_URL, $value->getData()->normalize());
    }

    private function createReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function createUrlAttribute(): void
    {
        $attribute = UrlAttribute::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            AttributeCode::fromString(self::ATTRIBUTE_CODE),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::empty(),
            Suffix::empty(),
            PreviewType::fromString('image')
        );
        $this->attributeRepository->create($attribute);
    }

    private function createRecord(): void
    {
        $this->recordRepository->create(
            Record::create(
                RecordIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::create(
                            self::REFERENCE_ENTITY_IDENTIFIER,
                            self::ATTRIBUTE_CODE,
                            self::FINGERPRINT
                        ),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        UrlData::fromString(self::OLD_URL)
                    ),
                ])
            )
        );
    }
}
