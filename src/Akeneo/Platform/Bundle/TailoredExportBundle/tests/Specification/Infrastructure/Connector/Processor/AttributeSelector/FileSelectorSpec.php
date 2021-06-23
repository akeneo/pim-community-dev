<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class FileSelectorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['pim_catalog_file', 'pim_catalog_image']);
    }

    public function it_returns_attribute_type_supported()
    {
        $fileAttribute = $this->createAttribute('pim_catalog_file');
        $imageAttribute = $this->createAttribute('pim_catalog_image');

        $this->supports(['type' => 'key'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'path'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'name'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'key'], $imageAttribute)->shouldReturn(true);
        $this->supports(['type' => 'path'], $imageAttribute)->shouldReturn(true);
        $this->supports(['type' => 'name'], $imageAttribute)->shouldReturn(true);
        $this->supports(['type' => 'code'], $fileAttribute)->shouldReturn(false);
        $this->supports(['type' => 'label'], $fileAttribute)->shouldReturn(false);
        $this->supports(['type' => 'code'], $imageAttribute)->shouldReturn(false);
        $this->supports(['type' => 'label'], $imageAttribute)->shouldReturn(false);
    }

    public function it_returns_empty_string_when_data_is_null(
        ValueInterface $value,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createAttribute('pim_catalog_file');
        $value->getData()->willReturn(null);

        $this->applySelection(['type' => 'key'], $entity, $fileAttribute, $value)
            ->shouldReturn('');
    }

    public function it_selects_the_key(
        ValueInterface $value,
        FileInfo $file,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createAttribute('pim_catalog_file');
        $value->getData()->willReturn($file);
        $file->getKey()->willReturn('f/e/w/t/file.jpg');

        $this->applySelection(['type' => 'key'], $entity, $fileAttribute, $value)
            ->shouldReturn('f/e/w/t/file.jpg');
    }

    public function it_selects_the_name(
        ValueInterface $value,
        FileInfo $file,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createAttribute('pim_catalog_file');
        $value->getData()->willReturn($file);
        $file->getOriginalFilename()->willReturn('file.jpg');

        $this->applySelection(['type' => 'name'], $entity, $fileAttribute, $value)
            ->shouldReturn('file.jpg');
    }

    public function it_selects_the_path(
        ValueInterface $value,
        FileInfo $file,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createAttribute('pim_catalog_file');
        $value->getData()->willReturn($file);
        $value->getScopeCode()->willReturn('mobile');
        $value->getLocaleCode()->willReturn('fr_FR');
        $entity->getIdentifier()->willReturn('product_identifier');
        $file->getOriginalFilename()->willReturn('file.jpg');

        $this->applySelection(['type' => 'path'], $entity, $fileAttribute, $value)
            ->shouldReturn('files/product_identifier/nice_attribute/fr_FR/mobile/file.jpg');
    }

    private function createAttribute(string $attributeType): Attribute
    {
        return new Attribute(
            'nice_attribute',
            $attributeType,
            [],
            false,
            false,
            null,
            null,
            null,
            $attributeType,
            []
        );
    }
}
