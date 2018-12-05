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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributeByIdentifierAndCode;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributeByIdentifierAndCode */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var null|Response */
    private $attributeForReferenceEntity;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeByIdentifierAndCode $findConnectorReferenceEntityAttributes,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorReferenceEntityAttributes = $findConnectorReferenceEntityAttributes;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the Description attribute that is part of the structure of the Brand reference entity$/
     */
    public function theDescriptionAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the Description attribute of the Brand reference entity$/
     */
    public function theConnectorRequestsTheDescriptionAttributeOfTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM returns the Description reference attribute$/
     */
    public function thePIMReturnsTheDescriptionReferenceAttribute()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests a given attribute of a non\-existent reference entity$/
     */
    public function theConnectorRequestsAGivenAttributeOfANonExistentReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Brand reference entity with some attributes$/
     */
    public function theBrandReferenceEntityWithSomeAttributes()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests a non\-existent attribute of a given reference entity$/
     */
    public function theConnectorRequestsANonExistentAttributeOfAGivenReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not exist for the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotExistForTheBrandReferenceEntity()
    {
        throw new PendingException();
    }
}
