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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributesByReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeOptionContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributesByReferenceEntityIdentifier */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributesByReferenceEntityIdentifier $findConnectorReferenceEntityAttributes,
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
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand reference entity$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the French option that is one of the options of the Nationality attribute$/
     */
    public function theFrenchOptionThatIsOneOfTheOptionsOfTheNationalityAttribute()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the French option of the Nationality attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsTheFrenchOptionOfTheNationalityAttributeForTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM returns the French option$/
     */
    public function thePIMReturnsTheFrenchOption()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Sales Area multiple options attribute that is part of the structure of the Brand reference entity$/
     */
    public function theSalesAreaMultipleOptionsAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Asia option that is one of the options of the Sales Area attribute$/
     */
    public function theAsiaOptionThatIsOneOfTheOptionsOfTheSalesAreaAttribute()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests the Asia option of the Sales Area attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsTheAsiaOptionOfTheSalesAreaAttributeForTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM returns the Asia option$/
     */
    public function thePIMReturnsTheAsiaOption()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand reference entity but has no options yet$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntityButHasNoOptionsYet()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector requests a non\-existent option for a given attribute for a given reference entity$/
     */
    public function theConnectorRequestsANonExistentOptionForAGivenAttributeForAGivenReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the option is non existent for the Nationality attribute and the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheOptionIsNonExistentForTheNationalityAttributeAndTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

}
