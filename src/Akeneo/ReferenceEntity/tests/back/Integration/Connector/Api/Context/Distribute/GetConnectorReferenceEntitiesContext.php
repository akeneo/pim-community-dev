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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Distribute;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorReferenceEntityItems;
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
use Webmozart\Assert\Assert;

class GetConnectorReferenceEntitiesContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'ReferenceEntity/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorReferenceEntityItems */
    private $findConnectorReferenceEntity;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var array */
    private $referenceEntityPages;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorReferenceEntityItems $findConnectorReferenceEntity,
        ReferenceEntityRepositoryInterface $referenceEntityRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorReferenceEntity = $findConnectorReferenceEntity;
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    /**
     * @Given /^7 reference entities in the PIM$/
     */
    public function referenceEntitiesInThePIM()
    {
        for ($i = 1; $i <= 7; $i++) {
            $rawIdentifier = sprintf('%s_%d', 'reference_entity', $i);
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($rawIdentifier);

            $imageInfo = new FileInfo();
            $imageInfo
                ->setOriginalFilename(sprintf('%s.jpg', $rawIdentifier))
                ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

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
    }

    /**
     * @When /^the connector requests all reference entities of the PIM$/
     */
    public function theConnectorRequestsAllReferenceEntitiesOfThePIM()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->referenceEntityPages = [];

        for ($page = 1; $page <= 3; $page++) {
            $this->referenceEntityPages[$page] = $this->webClientHelper->requestFromFile(
                $client,
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_reference_entities_page_%d.json",
                    $page
                )
            );
        }
    }

    /**
     * @Then /^the PIM returns the label and image properties of the 7 reference entities of the PIM$/
     */
    public function thePIMReturnsTheReferenceEntitiesOfThePIM()
    {
        for ($page = 1; $page <= 3; $page++) {
            Assert::keyExists($this->referenceEntityPages, $page, sprintf('The page %d has not been loaded', $page));

            $this->webClientHelper->assertJsonFromFile(
                $this->referenceEntityPages[$page],
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_reference_entities_page_%d.json",
                    $page
                )
            );
        }
    }
}
