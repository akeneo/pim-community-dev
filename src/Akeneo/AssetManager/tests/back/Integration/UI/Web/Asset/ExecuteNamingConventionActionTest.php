<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Fake\ProductLinkRuleLauncherSpy;
use Akeneo\AssetManager\Common\Fake\SecurityFacadeStub;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
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
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

use const false;
use const null;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionActionTest extends ControllerIntegrationTestCase
{
    private const EXECUTE_NAMING_CONVENTION_ROUTE = 'akeneo_asset_manager_asset_family_execute_naming_convention';
    private const EXECUTE_NAMING_CONVETIONS_ACL = 'akeneo_assetmanager_asset_family_execute_naming_conventions';
    private const EDIT_ASSET_FAMILY_ACL = 'akeneo_assetmanager_asset_family_edit';
    private const ASSET_FAMILY_IDENTIFIER = 'singer';
    private const ATTRIBUTE_IDENTIFIER = 'year_of_first_album';

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var SecurityFacadeStub */
    private $securityFacade;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->securityFacade = $this->get('oro_security.security_facade');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $this->allowExecuteNamingConventionsRights();
    }

    /** @test */
    public function it_executes_the_naming_convention_on_the_asset(): void
    {
        $this->createAssetFamilyWithNamingConvention(
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null
                ],
                'pattern' => '/.*\\_(?<year_of_first_album>.*)$/',
                'abort_asset_creation_on_error' => false,
            ]
        );
        $assetCodeToExecuteNamingConventionsOn = 'celine_dion_1975'; // 1975 is the year_of_first_album
        $this->createAssetWithCode($assetCodeToExecuteNamingConventionsOn);
        $this->assertYearOfFirstAlbumIsEmpty($assetCodeToExecuteNamingConventionsOn);

        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => self::ASSET_FAMILY_IDENTIFIER, 'assetCode' => $assetCodeToExecuteNamingConventionsOn],
            'POST'
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->assertYearOfFirstAlbumIs($assetCodeToExecuteNamingConventionsOn, '1975');
    }

    /** @test */
    // public function it_sends_an_error_message_when_the_execution_of_the_naming_convention_is_not_possible(): void
//{
//  // MaxLengthOfAttribute is 4 but naming convention tries to set 5 to it
//}

    /** @test */
    public function it_forbids_to_execute_naming_conventions_when_user_does_not_have_the_right_to_execute_naming_conventions(): void
    {
        $this->revokeExecuteNamingConventionsRights();
        $this->createAssetFamilyWithNamingConvention(
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null
                ],
                'pattern' => '/.*\\_(?<year_of_first_album>.*)$/',
                'abort_asset_creation_on_error' => false,
            ]
        );
        $assetCodeToExecuteNamingConventionsOn = 'celine_dion_1975'; // 1975 is the year_of_first_album
        $this->createAssetWithCode($assetCodeToExecuteNamingConventionsOn);
        $this->assertYearOfFirstAlbumIsEmpty($assetCodeToExecuteNamingConventionsOn);

        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            [
                'assetFamilyIdentifier' => self::ASSET_FAMILY_IDENTIFIER,
                'assetCode' => $assetCodeToExecuteNamingConventionsOn
            ],
            'POST'
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->assertYearOfFirstAlbumIsEmpty($assetCodeToExecuteNamingConventionsOn);
    }

    /** @test */
    public function it_forbids_to_execute_naming_conventions_when_user_does_not_have_the_permission_to_edit_asset_families(): void
    {
        $this->revokeEditAssetFamilyRights();
        $this->createAssetFamilyWithNamingConvention(
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null
                ],
                'pattern' => '/.*\\_(?<year_of_first_album>.*)$/',
                'abort_asset_creation_on_error' => false,
            ]
        );
        $assetCodeToExecuteNamingConventionsOn = 'celine_dion_1975'; // 1975 is the year_of_first_album
        $this->createAssetWithCode($assetCodeToExecuteNamingConventionsOn);
        $this->assertYearOfFirstAlbumIsEmpty($assetCodeToExecuteNamingConventionsOn);

        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            [
                'assetFamilyIdentifier' => self::ASSET_FAMILY_IDENTIFIER,
                'assetCode' => $assetCodeToExecuteNamingConventionsOn
            ],
            'POST'
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        $this->assertYearOfFirstAlbumIsEmpty($assetCodeToExecuteNamingConventionsOn);
    }
    /** @test */
    public function it_does_not_execute_naming_conventions_when_asset_family_is_not_found(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => 'unknown_asset_family', 'assetCode' => 'unknown_asset'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /** @test */
    public function it_does_not_execute_naming_conventions_when_asset_does_not_exist(): void
    {
        $this->createAssetFamilyWithNamingConvention([]);
        $this->webClientHelper->callRoute(
            $this->client,
            self::EXECUTE_NAMING_CONVENTION_ROUTE,
            ['assetFamilyIdentifier' => self::ASSET_FAMILY_IDENTIFIER, 'assetCode' => 'unknown_asset'],
            'POST'
        );
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    private function allowExecuteNamingConventionsRights(): void
    {
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, true);
        $this->securityFacade->setIsGranted(self::EXECUTE_NAMING_CONVETIONS_ACL, true);
    }

    private function revokeEditAssetFamilyRights(): void
    {
        $this->securityFacade->setIsGranted(self::EDIT_ASSET_FAMILY_ACL, false);
    }

    private function revokeExecuteNamingConventionsRights(): void
    {
        $this->securityFacade->setIsGranted(self::EXECUTE_NAMING_CONVETIONS_ACL, false);
    }

    private function createAssetWithCode($assetCode): void
    {
        $celineAsset = Asset::create(
            AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, $assetCode, 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($assetCode),
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($celineAsset);
    }

    private function assertYearOfFirstAlbumIsEmpty($assetCode): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($assetCode)
        );
        $value = $asset->findValue(ValueKey::create(
            AttributeIdentifier::fromString(self::ATTRIBUTE_IDENTIFIER),
            ChannelReference::noReference(),
            LocaleReference::noReference()
        ));
        self::assertNull($value);
    }

    private function assertYearOfFirstAlbumIs(string $assetCode, string $expectedValue): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($assetCode)
        );
        $value = $asset->findValue(ValueKey::create(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_IDENTIFIER, 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference()
        ));
        self::assertNotNull($value, sprintf('Expected to have value "%s" for attribute "%s"', $expectedValue, self::ATTRIBUTE_IDENTIFIER));
        $actualValue = $value->getData()->normalize();
        self::assertEquals($actualValue, $expectedValue);
    }

    private function createAssetFamilyWithNamingConvention($namingConvention): void
    {
        $singer = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'Singer', 'fr_FR' => 'Chanteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        )->withNamingConvention(NamingConvention::createFromNormalized($namingConvention));
        $this->assetFamilyRepository->create($singer);

        $yearOfFirstAlbum = TextAttribute::createText(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ATTRIBUTE_IDENTIFIER, 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::ATTRIBUTE_IDENTIFIER),
            LabelCollection::fromArray(['en_US' => 'Year Of First Album']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($yearOfFirstAlbum);
    }
}
