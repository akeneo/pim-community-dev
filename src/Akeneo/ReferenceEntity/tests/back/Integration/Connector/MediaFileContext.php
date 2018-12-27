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

namespace Akeneo\ReferenceEntity\Integration\Connector;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFilesystemProviderStub;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryMediaFileRepository;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'MediaFile/Connector/';

    private const KARTELL_IMAGE_FILE_PATH = '0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg';
    private const KARTELL_IMAGE_BINARY = 'This represents the binary of an image';
    private const KARTELL_IMAGE_MIMETYPE = 'image/jpeg';
    private const KARTELL_IMAGE_NAME = 'kartell.jpg';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFilesystemProviderStub */
    private $filesystemProvider;

    /** @var InMemoryMediaFileRepository */
    private $mediaFileRepository;

    /** @var StreamedResponse */
    private $downloadResponse;

    /** @var Response */
    private $notFoundResponse;

    /** @var string */
    private $downloadedMediaFile;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryMediaFileRepository $mediaFileRepository,
        InMemoryFilesystemProviderStub $filesystemProvider
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->mediaFileRepository = $mediaFileRepository;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * @Given the Kartell record of the Brand reference entity
     */
    public function theKartellRecordOfTheBrandReferenceEntity()
    {
    }

    /**
     * @Given the photo attribute enriched with an image
     */
    public function thePhotoAttributeEnrichedWithAnImage()
    {
        $imageFile = new FileInfo();
        $imageFile->setKey(self::KARTELL_IMAGE_FILE_PATH);
        $imageFile->setMimeType(self::KARTELL_IMAGE_MIMETYPE);
        $imageFile->setOriginalFilename(self::KARTELL_IMAGE_NAME);

        $this->mediaFileRepository->save($imageFile);

        $fileSystem = $this->filesystemProvider->getFileSystem('catalogStorage');
        $fileSystem->write(self::KARTELL_IMAGE_FILE_PATH, self::KARTELL_IMAGE_BINARY);
    }

    /**
     * @When the connector requests to download the image of the photo attribute of the Kartell record
     */
    public function theConnectorRequestsToDownloadTheImageOfThePhotoAttributeOfTheKartellRecord()
    {
        $client = $this->clientFactory->logIn('julia');

        ob_start();
        $this->downloadResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_image_download.json"
        );

        $this->downloadedMediaFile = ob_get_clean();
    }

    /**
     * @Then the PIM returns the image of the photo attribute of the Kartell record
     */
    public function thePimReturnsTheImageOfThePhotoAttributeOfTheKartellRecord()
    {
        $this->webClientHelper->assertStreamedResponseFromFile(
            $this->downloadResponse,
            $this->downloadedMediaFile,
            self::REQUEST_CONTRACT_DIR ."successful_image_download.json"
        );
    }

    /**
     * @When the connector requests to download the image of the photo attribute of the Kartell record giving the wrong code
     */
    public function theConnectorRequestsToDownloadTheImageOfThePhotoAttributeOfTheKartellRecordGivingTheWrongCode()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->notFoundResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."not_found_image_download.json"
        );
    }

    /**
     * @Then the PIM notifies the connector that the image does not exist
     */
    public function thePimNotifiesTheConnectorThatTheImageDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->notFoundResponse,
            self::REQUEST_CONTRACT_DIR ."not_found_image_download.json"
        );
    }
}
