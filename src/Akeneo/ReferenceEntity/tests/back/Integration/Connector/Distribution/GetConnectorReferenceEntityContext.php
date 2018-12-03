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

namespace Akeneo\ReferenceEntity\Integration\Connector\Distribution;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorReferenceEntityContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'ReferenceEntity/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier */
    private $findConnectorReferenceEntity;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|Response */
    private $existentReferenceEntity;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorReferenceEntityByReferenceEntityIdentifier $findConnectorReferenceEntity,
        ReferenceEntityRepositoryInterface $referenceEntityRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    /**
     * @Given /^the Brand reference entity$/
     */
    public function theBrandReferenceEntity(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('brand.jpg')
            ->setKey('5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_brand.jpg');

        $referenceEntity = new ConnectorReferenceEntity(
            $referenceEntityIdentifier,
            LabelCollection::fromArray(['fr_FR' => 'Marque']),
            Image::fromFileInfo($imageInfo)
        );

        $this->findConnectorReferenceEntity->save(
            $referenceEntityIdentifier,
            $referenceEntity
        );

        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When /^the connector requests the Brand reference entity$/
     */
    public function theConnectorRequestsTheBrandReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->existentReferenceEntity = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_brand_reference_entity.json"
        );
    }

    /**
     * @Then /^the PIM returns the label and image properties Brand reference entity$/
     */
    public function thePIMReturnsTheBrandReferenceEntity(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->existentReferenceEntity,
            self::REQUEST_CONTRACT_DIR . "successful_brand_reference_entity.json"
        );
    }
}
