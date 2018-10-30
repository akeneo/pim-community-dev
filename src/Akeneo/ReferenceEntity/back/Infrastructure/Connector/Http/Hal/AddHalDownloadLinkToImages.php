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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Add download links at HAL format to a normalized record for each image (as main image or as value)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AddHalDownloadLinkToImages
{
    /** @var Router */
    private $router;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    public function __construct(
        Router $router,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->router = $router;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): array
    {
        if (!empty($normalizedRecord['main_image'])) {
            $normalizedRecord = $this->addDownloadLinkToMainImage($normalizedRecord);
        }

        $imageAttributeCodes = $this->getImageAttributeCodes($referenceEntityIdentifier);

        foreach ($normalizedRecord['values'] as $attributeCode => $values) {
            if (in_array($attributeCode, $imageAttributeCodes)) {
                $normalizedRecord['values'][$attributeCode] = $this->addDownloadLinksToImageValues($values);
            }
        }

        return $normalizedRecord;
    }

    private function getImageAttributeCodes(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributes = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);
        $imageAttributeCodes = [];

        foreach ($attributes as $attribute) {
            if ($attribute instanceof ImageAttribute) {
                $imageAttributeCodes[] = $attribute->getCode();
            }
        }

        return $imageAttributeCodes;
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
            'akeneo_reference_entities_file_rest_connector_download',
            ['fileCode' => $imageCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
