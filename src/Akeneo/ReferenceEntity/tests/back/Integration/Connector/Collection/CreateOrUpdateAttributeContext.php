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

use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class CreateOrUpdateAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var null|string */
    private $requestContract;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
    }

    /**
     * @Given the Color reference entity existing both in the ERP and in the PIM
     */
    public function theColorReferenceEntityExistingBothInTheErpAndInThePim()
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('color'),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);

        $this->requestContract = 'successful_main_color_reference_entity_attribute_creation.json';
    }

    /**
     * @Given the Main Color attribute that is only part of the structure of the Color reference entity in the ERP but not in the PIM
     */
    public function theMainColorAttributeThatIsOnlyPartOfTheStructureOfTheColorReferenceEntityInTheERPButNotInThePIM()
    {
    }

    /**
     * @When the connector collects the Main Color attribute of the Color reference entity from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsTheMainColorAttributeOfTheColorReferenceEntityFromTheERPToSynchronizeItWithThePIM()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }
}
