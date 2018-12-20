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

namespace Akeneo\ReferenceEntity\Integration\Connector\Collection;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
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

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
    }

    /**
     * @Given the Brand reference entity existing in the ERP but not in the PIM
     */
    public function theBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_brand_reference_entity_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
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
    public function theReferenceEntityIsCreated ()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_reference_entity_creation.json'
        );

        $brand = $this->referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('brand'));
        $expectedBrand = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            Image::createEmpty()
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
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

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

        $brand = $this->referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('brand'));
        $expectedBrand = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            Image::createEmpty()
        );

        Assert::assertEquals($brand, $expectedBrand);
    }
}
