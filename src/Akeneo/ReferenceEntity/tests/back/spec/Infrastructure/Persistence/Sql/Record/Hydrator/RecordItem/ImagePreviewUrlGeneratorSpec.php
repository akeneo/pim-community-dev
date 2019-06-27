<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem;

use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ImagePreviewUrlGenerator;
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

        $router->generate('akeneo_reference_entities_image_preview', ['data' => $data, 'attributeIdentifier' => $attributeIdentifier, 'type' => $previewType])
               ->willReturn('/url/to/preview');

        $this->generate($data, $attributeIdentifier, $previewType);
    }
}
