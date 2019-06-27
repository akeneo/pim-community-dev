<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Symfony\Component\Routing\Router;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ImagePreviewUrlGenerator
{
    private const URL_ATTRIBUTE_PREVIEW_ENDPOINT = 'akeneo_reference_entities_image_preview';

    /** @var Router */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function generate(string $data, string $attributeIdentifier, string $type): string
    {
        return $this->router->generate(
            self::URL_ATTRIBUTE_PREVIEW_ENDPOINT,
            [
                'data'                => urlencode($data),
                'attributeIdentifier' => $attributeIdentifier,
                'type'                => $type
            ]
        );
    }
}
