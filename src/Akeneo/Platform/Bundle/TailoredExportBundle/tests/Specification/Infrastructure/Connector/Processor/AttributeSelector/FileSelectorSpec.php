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
        $this->beConstructedWith(['pim_catalog_file']);
    }

    public function it_returns_attribute_type_supported()
    {
        $fileAttribute = $this->createFileAttribute();

        $this->supports(['type' => 'key'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'path'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'name'], $fileAttribute)->shouldReturn(true);
        $this->supports(['type' => 'code'], $fileAttribute)->shouldReturn(false);
        $this->supports(['type' => 'label'], $fileAttribute)->shouldReturn(false);
    }

    public function it_returns_empty_string_when_data_is_null(
        ValueInterface $value,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createFileAttribute();
        $value->getData()->willReturn(null);

        $this->applySelection(['type' => 'key'], $entity, $fileAttribute, $value)
            ->shouldReturn('');
    }

    public function it_selects_the_key(
        ValueInterface $value,
        FileInfo $file,
        ProductInterface $entity
    ) {
        $fileAttribute = $this->createFileAttribute();
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
        $fileAttribute = $this->createFileAttribute();
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
        $fileAttribute = $this->createFileAttribute();
        $value->getData()->willReturn($file);
        $value->getScopeCode()->willReturn('mobile');
        $value->getLocaleCode()->willReturn('fr_FR');
        $entity->getIdentifier()->willReturn('product_identifier');
        $file->getOriginalFilename()->willReturn('file.jpg');

        $this->applySelection(['type' => 'path'], $entity, $fileAttribute, $value)
            ->shouldReturn('files/product_identifier/file_attribute/fr_FR/mobile/file.jpg');
    }

    private function createFileAttribute(): Attribute
    {
        return new Attribute(
            'file_attribute',
            'pim_catalog_file',
            [],
            false,
            false,
            null,
            null,
            null,
            'file',
            []
        );
    }
}
