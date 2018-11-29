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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Hal;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Add download links at HAL format to a list of normalized records for each record image (as main image or as value)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AddHalDownloadLinkToRecordImages
{
    /** @var Router */
    private $router;

    /** @var FindImageAttributeCodesInterface */
    private $findImageAttributeCodes;

    public function __construct(
        Router $router,
        FindImageAttributeCodesInterface $findImageAttributeCodes
    ) {
        $this->router = $router;
        $this->findImageAttributeCodes = $findImageAttributeCodes;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecords): array
    {
        $imageAttributeCodes = ($this->findImageAttributeCodes)($referenceEntityIdentifier);

        return array_map(function ($normalizedRecord) use ($imageAttributeCodes) {
            return $this->addDownloadLinkToNormalizedRecord($normalizedRecord, $imageAttributeCodes);
        }, $normalizedRecords);
    }

    private function addDownloadLinkToNormalizedRecord(array $normalizedRecord, array $imageAttributeCodes): array
    {
        if (!empty($normalizedRecord['main_image'])) {
            $normalizedRecord = $this->addDownloadLinkToMainImage($normalizedRecord);
        }

        foreach ($imageAttributeCodes as $imageAttributeCode) {
            $imageAttributeCode = (string) $imageAttributeCode;
            if (isset($normalizedRecord['values'][$imageAttributeCode])) {
                $normalizedRecord['values'][$imageAttributeCode] = $this->addDownloadLinksToImageValues(
                    $normalizedRecord['values'][$imageAttributeCode]
                );
            }
        }

        return $normalizedRecord;
    }

    private function addDownloadLinkToMainImage(array $normalizedRecord): array
    {
        $mainImageUrl = $this->generateImageUrl($normalizedRecord['main_image']);
        $mainImageLink = new Link('main_image_download', $mainImageUrl);
        $normalizedRecord['_links'] = ($normalizedRecord['_links'] ?? []) + $mainImageLink->toArray();

        return $normalizedRecord;
    }

    private function addDownloadLinksToImageValues(array $values): array
    {
        return array_map(function (array $value) {
            if (!empty($value['data'])) {
                $url = $this->generateImageUrl($value['data']);
                $link = new Link('download', $url);
                $value['_links'] = $link->toArray();
            }
            return $value;
        }, $values);
    }

    private function generateImageUrl(string $imageCode): string
    {
        return $this->router->generate(
            'akeneo_reference_entities_media_file_rest_connector_get',
            ['fileCode' => $imageCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
