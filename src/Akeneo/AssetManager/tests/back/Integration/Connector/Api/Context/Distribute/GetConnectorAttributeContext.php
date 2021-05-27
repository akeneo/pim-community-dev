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

namespace Akeneo\AssetManager\Integration\Connector\Api\Context\Distribute;

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributeByIdentifierAndCode;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private InMemoryFindConnectorAttributeByIdentifierAndCode $findConnectorAttribute;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private ?Response $attributeForAssetFamily = null;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeByIdentifierAndCode $findConnectorAssetFamilyAttributes,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttribute = $findConnectorAssetFamilyAttributes;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the Description attribute that is part of the structure of the Brand asset family$/
     */
    public function theDescriptionAttributeThatIsPartOfTheStructureOfTheBrandAssetFamily()
    {
        $this->createBrandAssetFamily();
    }

    /**
     * @When /^the connector requests the Description attribute of the Brand asset family$/
     */
    public function theConnectorRequestsTheDescriptionAttributeOfTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->attributeForAssetFamily = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_brand_asset_family_description_attribute.json"
        );
    }

    /**
     * @Then /^the PIM returns the Description reference attribute$/
     */
    public function thePIMReturnsTheDescriptionReferenceAttribute()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->attributeForAssetFamily,
            self::REQUEST_CONTRACT_DIR . "successful_brand_asset_family_description_attribute.json"
        );
    }

    /**
     * @Given /^the Brand asset family with some attributes$/
     */
    public function theBrandAssetFamilyWithSomeAttributes()
    {
        $this->createBrandAssetFamily();
    }

    private function createBrandAssetFamily()
    {
        $assetFamilyIdentifier = 'brand_test';
        $attributeCode = 'description';

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeCode, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $this->attributeRepository->create($textAttribute);

        $textConnectorAttribute = new ConnectorAttribute(
            $textAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean($textAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($textAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'max_length' => $textAttribute->getMaxLength()->intValue(),
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => null,
                'regular_expression' => null
            ]
        );

        $this->findConnectorAttribute->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $textAttribute->getCode(),
            $textConnectorAttribute
        );

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }
}
