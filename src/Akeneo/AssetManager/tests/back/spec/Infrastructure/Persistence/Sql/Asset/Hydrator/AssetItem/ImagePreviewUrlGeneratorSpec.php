<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Router;

class ImagePreviewUrlGeneratorSpec extends ObjectBehavior
{
    public function let(Router $router)
    {
        $this->beConstructedWith($router);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ImagePreviewUrlGenerator::class);
    }

    public function it_generates_an_url_to_get_the_preview_of_a_value(Router $router)
    {
        $data = 'house_251.png';
        $attributeIdentifier = 'front_view';
        $previewType = 'thumbnail';

        $router->generate('akeneo_asset_manager_image_preview', ['data' => $data, 'attributeIdentifier' => $attributeIdentifier, 'type' => $previewType])
               ->willReturn('/url/to/preview');

        $this->generate($data, $attributeIdentifier, $previewType);
    }
}
