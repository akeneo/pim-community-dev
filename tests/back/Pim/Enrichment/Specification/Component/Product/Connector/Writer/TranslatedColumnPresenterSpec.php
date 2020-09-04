<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer;

use PhpSpec\ObjectBehavior;

class TranslatedColumnPresenterSpec extends ObjectBehavior
{
    function it_only_handle_export_with_translated_header()
    {
        $data = [
            'sku--[sku]',
            'description--Description',
            'name--Nom',
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => false])->shouldReturn($data);
    }

    function it_format_duplicated_translations()
    {
        $data = [
            'brand--Marque',
            'camera_brand--Marque',
            'size--Taille',
            'objective_size--Taille'
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => true])->shouldReturn([
            'Marque - brand',
            'Marque - camera_brand',
            'Taille - size',
            'Taille - objective_size',
        ]);
    }

    function it_remove_code_when_translation_is_not_duplicated()
    {
        $data = [
            'sku--[sku]',
            'description--Description',
            'name--Nom',
            'brand--Marque',
            'camera_brand--Marque',
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => true])->shouldReturn([
            '[sku]',
            'Description',
            'Nom',
            'Marque - brand',
            'Marque - camera_brand',
        ]);
    }
}
