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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Collect;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFileExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryGetAttributeIdentifier;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use AkeneoEnterprise\Test\Acceptance\Permission\InMemory\SecurityFacadeStub;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateReferenceEntityContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'ReferenceEntity/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var InMemoryFindFileDataByFileKey */
    private $findFileData;

    /** @var InMemoryFileExists */
    private $fileExists;

    /** @var InMemoryGetAttributeIdentifier */
    private $getAttributeIdentifier;

    private SecurityFacadeStub $securityFacade;

    private TestLogger $apiAclLogger;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        InMemoryFindFileDataByFileKey $findFileData,
        InMemoryFileExists $fileExists,
        InMemoryGetAttributeIdentifier $getAttributeIdentifier,
        SecurityFacadeStub $securityFacade,
        TestLogger $apiAclLogger
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->findFileData = $findFileData;
        $this->fileExists = $fileExists;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->securityFacade = $securityFacade;
        $this->apiAclLogger = $apiAclLogger;
    }

    /**
     * @BeforeScenario
     */
    public function before()
    {
        $this->securityFacade->setIsGranted('pim_api_entity_edit', true);
        $this->securityFacade->setIsGranted('pim_api_entity_list', true);
        $this->securityFacade->setIsGranted('pim_api_record_edit', true);
        $this->securityFacade->setIsGranted('pim_api_record_list', true);
    }

    /**
     * @Given the Brand reference entity existing in the ERP but not in the PIM
     */
    public function theBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_brand_reference_entity_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $image = $this->getBrandImage();
        $this->fileExists->save($image->getKey());
        $this->findFileData->save($image->normalize());
    }

    /**
     * @When the connector collects the properties of the Brand reference entity from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThePropertiesOfTheBrandReferenceEntityFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the reference entity is created with its properties in the PIM with the information from the ERP
     */
    public function theReferenceEntityIsCreated()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_reference_entity_creation.json'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('image')
        );

        $brand = $this->referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('brand'));
        $expectedBrand = ReferenceEntity::createWithAttributes(
            $referenceEntityIdentifier,
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            $this->getBrandImage(),
            AttributeAsLabelReference::fromAttributeIdentifier($labelIdentifier),
            AttributeAsImageReference::fromAttributeIdentifier($mainImageIdentifier)
        );

        Assert::assertEquals($brand, $expectedBrand);
    }

    /**
     * @Given the Brand reference entity existing in the ERP and the PIM with different properties
     */
    public function theBrandReferenceEntityExistingInTheErpAndInThePimWithDifferentProperties()
    {
        $this->requestContract = 'successful_brand_reference_entity_update.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $image = $this->getBrandImage();
        $this->fileExists->save($image->getKey());
        $this->findFileData->save($image->normalize());

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            $image
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When the connector collects the Brand reference entity from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsTheBrandReferenceEntityFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the properties of the reference entity are correctly synchronized in the PIM with the information from the ERP
     */
    public function thePropertiesOfTheReferenceEntityAreCorrectlySynchornizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_reference_entity_update.json'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('image')
        );

        $brand = $this->referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('brand'));
        $expectedBrand = ReferenceEntity::createWithAttributes(
            $referenceEntityIdentifier,
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            $this->getBrandImage(),
            AttributeAsLabelReference::fromAttributeIdentifier($labelIdentifier),
            AttributeAsImageReference::fromAttributeIdentifier($mainImageIdentifier)
        );

        Assert::assertEquals($brand, $expectedBrand);
    }

    /**
     * @Given some reference entities
     */
    public function someReferenceEntities()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('de_DE'));

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When the connector collects a reference entity that has an invalid format
     */
    public function collectAReferenceEntityWithAnInvalidFormat()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_reference_entity_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the reference entity has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheReferenceEntityHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_reference_entity_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects a reference entity whose data does not comply with the business rules
     */
    public function theConnectorCollectsAReferenceEntityWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_reference_entity_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the reference entity has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheReferenceEntityHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_reference_entity_for_invalid_data.json'
        );
    }

    /**
     * @When the connector collects these reference entities from the ERP to synchronize them with the PIM without permission
     */
    public function theConnectorCollectsTheseReferenceEntitiesFromTheErpToSynchronizeThemWithThePimWithoutPermission()
    {
        $this->securityFacade->setIsGranted('pim_api_entity_edit', false);

        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'forbidden_brand_reference_entities_synchronization.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about missing permissions for collecting these reference entities from the ERP to synchronize them with the PIM
     */
    public function thePimNotifiesTheConnectorAboutMissingPermissions()
    {
        /**
         * TODO CXP-923: Assert 403 instead of success & remove logger assertion
         */
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'forbidden_brand_reference_entities_synchronization.json'
        );
        Assert::assertTrue(
            $this->apiAclLogger->hasWarning('User "julia" with roles ROLE_USER is not granted "pim_api_entity_edit"'),
            'Expected warning not found in the logs.'
        );
    }

    private function getBrandImage(): Image
    {
        $imageFileInfo = (new FileInfo())
            ->setKey('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_brand.png')
            ->setOriginalFilename('brand.png');

        $image = Image::fromFileInfo($imageFileInfo);

        return $image;
    }
}
