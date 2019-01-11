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
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;

class CreateOrUpdateAttributeOptionContext implements Context
{
    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

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
     * @Given /^the Brand reference entity reference entity existing both in the ERP and in the PIM$/
     */
    public function theBrandReferenceEntityReferenceEntityExistingBothInTheERPAndInThePIM()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Sales area attribute existing both in the ERP and in the PIM$/
     */
    public function theSalesAreaAttributeExistingBothInTheERPAndInThePIM()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the USA attribute option that only exist in the ERP but not in the PIM$/
     */
    public function theUSAAttributeOptionThatOnlyExistInTheERPButNotInThePIM()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector collects the USA attribute option of the Sales area Attribute of the Brand reference entity from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheUSAAttributeOptionOfTheSalesAreaAttributeOfTheBrandReferenceEntityFromTheERPToSynchronizeItWithThePIM()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the USA attribute option of the Sales area attribute is added to the structure of the Brand reference entity in the PIM with the properties coming from the ERP$/
     */
    public function theUSAAttributeOptionOfTheSalesAreaAttributeIsAddedToTheStructureOfTheBrandReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector collects an attribute option of a non\-existent reference entity$/
     */
    public function theConnectorCollectsAnAttributeOptionOfANonExistentReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @Given /^some attributes that structure the Brand reference entity$/
     */
    public function someAttributesThatStructureTheBrandReferenceEntity()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector collects an attribute option of a non\-existent attribute$/
     */
    public function theConnectorCollectsAnAttributeOptionOfANonExistentAttribute()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not exist$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotExist()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector collects a non existent attribute option$/
     */
    public function theConnectorCollectsANonExistentAttributeOption()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute option does not exist$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeOptionDoesNotExist()
    {
        throw new PendingException();
    }

    /**
     * @Given /^the Color attribute that structures the Brand reference entity and whose type is text$/
     */
    public function theColorAttributeThatStructuresTheBrandReferenceEntityAndWhoseTypeIsText()
    {
        throw new PendingException();
    }

    /**
     * @When /^the connector collects an attribute option of an attribute that does not accept options$/
     */
    public function theConnectorCollectsAnAttributeOptionOfAnAttributeThatDoesNotAcceptOptions()
    {
        throw new PendingException();
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does accept options$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesAcceptOptions()
    {
        throw new PendingException();
    }
}
