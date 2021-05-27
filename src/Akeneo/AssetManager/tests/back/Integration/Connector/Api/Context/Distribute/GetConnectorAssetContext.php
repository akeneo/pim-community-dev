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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetByAssetFamilyAndCode;
use Akeneo\AssetManager\Common\Fake\InMemoryMediaFileRepository;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFilesystemProviderStub;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetConnectorAssetContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Asset/Connector/Distribute/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private InMemoryFindConnectorAssetByAssetFamilyAndCode $findConnectorAsset;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ?Response $existentAsset = null;

    private ?Response $nonExistentAsset = null;

    private AttributeRepositoryInterface $attributeRepository;

    private InMemoryMediaFileRepository $mediaFileRepository;

    private InMemoryFilesystemProviderStub $filesystemProvider;

    private ?Response $mediaFileDownloadResponse = null;

    /** @var null|string */
    private $downloadedMediaFile;

    private ?Response $imageNotFoundResponse = null;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAssetByAssetFamilyAndCode $findConnectorAsset,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        InMemoryMediaFileRepository $mediaFileRepository,
        InMemoryFilesystemProviderStub $filesystemProvider
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAsset = $findConnectorAsset;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->mediaFileRepository = $mediaFileRepository;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * @Given /^the ([\S]+) asset for the ([\S]+) asset family$/
     */
    public function theAssetForTheAssetFamily(string $referenceCode, string $assetFamilyIdentifier): void
    {
        $asset = new ConnectorAsset(
            AssetCode::fromString($referenceCode),
            [
                'label' => [
                    [
                        'channel' => null,
                        'locale'  => 'fr_FR',
                        'value'   => 'A label'
                    ]
                ],
                'name' => [
                    [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'data' => 'My Name'
                    ],
                    [
                        'channel' => 'tablet',
                        'locale' => null,
                        'data' => 'My Tablet Name'
                    ]
                ],
                'cover_image' => [
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg'
                    ]
                ]
            ]
        );
        $this->findConnectorAsset->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AssetCode::fromString($referenceCode),
            $asset
        );

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        $this->loadNameAttribute();
        $this->loadCoverMediaFileAttribute();
    }

    /**
     * @When /^the connector requests the ([\S]+) asset for the ([\S]+) asset family$/
     */
    public function theConnectorRequestsAssetForAssetFamily(string $referenceCode, string $assetFamilyIdentifier): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->existentAsset = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . sprintf("successful_%s_asset.json", strtolower($referenceCode))
        );
    }

    /**
     * @Then /^the PIM returns the ([\S]+) asset of the ([\S]+) asset family$/
     */
    public function thePimReturnsAssetFamily(string $referenceCode)
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->existentAsset,
            self::REQUEST_CONTRACT_DIR . sprintf("successful_%s_asset.json", strtolower($referenceCode))
        );
    }

    /**
     * @Given /^the ([\S]+) asset family with some assets$/
     */
    public function theAssetFamilyWithSomeAssets(string $assetFamilyIdentifier): void
    {
        $assetFamilyIdentifier = strtolower($assetFamilyIdentifier);
        for ($i = 0; $i < 10 ; $i++) {
            $asset = new ConnectorAsset(
                AssetCode::fromString('asset_code_' . $i),
                []
            );
            $this->findConnectorAsset->save(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AssetCode::fromString('asset_code_' . $i),
                $asset
            );
        }

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When /^the connector requests for a non-existent asset for the ([\S]+) asset family$/
     */
    public function theConnectorRequestsForANonExistentAssetForTheAssetFamily(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->nonExistentAsset = $this->webClientHelper->requestFromFile($client, self::REQUEST_CONTRACT_DIR . "not_found_asset.json");
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset does not exist
     */
    public function thePIMNotifiesAnErrorIndicatingThatTheAssetDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile($this->nonExistentAsset, self::REQUEST_CONTRACT_DIR . "not_found_asset.json");
    }

    /**
     * @Given some asset families with some assets
     */
    public function someAssetFamiliesWithSomeAssets(): void
    {
        for ($i = 0; $i < 10 ; $i++) {
            for ($j = 0; $j < 10 ; $j++) {
                $asset = new ConnectorAsset(
                    AssetCode::fromString(sprintf('asset_code_%s_%s', $i, $j)),
                    []
                );
                $this->findConnectorAsset->save(
                    AssetFamilyIdentifier::fromString(sprintf('asset_family_%s', $i)),
                    AssetCode::fromString(sprintf('asset_code_%s_%s', $i, $j)),
                    $asset
                );
            }

            $assetFamily = AssetFamily::create(
                AssetFamilyIdentifier::fromString(sprintf('asset_family_%s', $i)),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            );

            $this->assetFamilyRepository->create($assetFamily);
        }
    }

    /**
     * @Given /^the Kartell asset of the Brand asset family with a media file in an attribute value$/
     */
    public function theKartellAssetOfTheBrandAssetFamilyWithAMediaFileInAnAttributeValue()
    {
        $mediaFile = new FileInfo();
        $mediaFile->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');
        $mediaFile->setMimeType('image/jpeg');
        $mediaFile->setOriginalFilename('kartell.jpg');

        $this->mediaFileRepository->save($mediaFile);

        $fileSystem = $this->filesystemProvider->getFileSystem(Storage::FILE_STORAGE_ALIAS);
        $fileSystem->write('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg', 'This represents the binary of a media file');
    }

    /**
     * @When /^the connector requests to download the media file of this attribute value$/
     */
    public function theConnectorRequestsToDownloadTheMediaFileOfThisAttributeValue()
    {
        $client = $this->clientFactory->logIn('julia');

        ob_start();
        $this->mediaFileDownloadResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_kartell_asset_media_file_download.json"
        );

        $this->downloadedMediaFile = ob_get_clean();
    }

    /**
     * @Then /^the PIM returns the media file binary of this attribute value$/
     */
    public function thePIMReturnsTheMediaFileBinaryOfThisAttributeValue()
    {
        $this->webClientHelper->assertStreamedResponseFromFile(
            $this->mediaFileDownloadResponse,
            $this->downloadedMediaFile,
            self::REQUEST_CONTRACT_DIR ."successful_kartell_asset_media_file_download.json"
        );
    }

    /**
     * @When /^the connector requests to download a non existent media file$/
     */
    public function theConnectorRequestsToDownloadANonExistentMediaFile()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->imageNotFoundResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."not_found_image_download.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector that the media file does not exist$/
     */
    public function thePIMNotifiesTheConnectorThatTheMediaFileDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->imageNotFoundResponse,
            self::REQUEST_CONTRACT_DIR ."not_found_image_download.json"
        );
    }

    private function loadNameAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadCoverMediaFileAttribute(): void
    {
        $image = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'cover_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('cover_image'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($image);
    }
}
