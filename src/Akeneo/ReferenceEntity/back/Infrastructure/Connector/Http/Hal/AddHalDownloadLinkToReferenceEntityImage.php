<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class AddHalDownloadLinkToReferenceEntityImage
{
    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function __invoke(array $normalizedReferenceEntity): array
    {
        $imageUrl = $this->generateImageUrl($normalizedReferenceEntity['image']);
        $imageLink = new Link('image_download', $imageUrl);
        $normalizedReferenceEntity['_links'] = ($normalizedReferenceEntity['_links'] ?? []) + $imageLink->toArray();

        return $normalizedReferenceEntity;
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
