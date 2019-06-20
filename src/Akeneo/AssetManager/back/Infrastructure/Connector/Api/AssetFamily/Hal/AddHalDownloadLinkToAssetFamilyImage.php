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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\Hal;

use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToReferenceEntityImage
{
    /** @var Router */
    private $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function __invoke(array $normalizedReferenceEntity): array
    {
        if (!empty($normalizedReferenceEntity['image'])) {
            $imageUrl = $this->generateImageUrl($normalizedReferenceEntity['image']);
            $imageLink = new Link('image_download', $imageUrl);
            $normalizedReferenceEntity['_links'] = ($normalizedReferenceEntity['_links'] ?? []) + $imageLink->toArray();
        }

        return $normalizedReferenceEntity;
    }

    private function generateImageUrl(string $imageCode): string
    {
        return $this->router->generate(
            'akeneo_reference_entities_media_file_rest_connector_download',
            ['fileCode' => $imageCode],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
