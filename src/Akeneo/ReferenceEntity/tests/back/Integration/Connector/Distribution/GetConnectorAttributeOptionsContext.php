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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributeOptions;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeOptionsContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributeOptions */
    private $findConnectorAttributeOptions;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ConnectorReferenceEntity */
    private $referenceEntity;

    /** @var OptionAttribute */
    private $singleOptionAttribute;

    /** @var OptionCollectionAttribute */
    private $multiOptionAttribute;

    /** @var null|Response **/
    private $optionResponse;

    /** @var null|Response **/
    private $multiOptionResponse;

    /** @var null|Response **/
    private $nonExistentAttributeOptionResponse;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeOptions $findConnectorAttributeOptions,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttributeOptions = $findConnectorAttributeOptions;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the 4 options of the Nationality single option attribute$/
     */
    public function theOptionsOfTheNationalitySingleOptionAttribute($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests all the options of the Nationality attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheNationalityAttributeForTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM returns the 4 options of the Nationality attributes for the Brand reference entity$/
     */
    public function thePIMReturnsTheOptionsOfTheNationalityAttributesForTheBrandReferenceEntity($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the 4 options of the Sales Area multiple options attribute$/
     */
    public function theOptionsOfTheSalesAreaMultipleOptionsAttribute($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests all the options of the Sales Area attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheSalesAreaAttributeForTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the options of an attribute for a non\-existent reference entity$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeForANonExistentReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Brand reference entity with no attribute in its structure$/
     */
    public function theBrandReferenceEntityWithNoAttributeInItsStructure()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the options of an attribute that is not part of the structure of the given reference entity$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeThatIsNotPartOfTheStructureOfTheGivenReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute is not part of the structure of the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeIsNotPartOfTheStructureOfTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

}
