<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer;

use PhpSpec\ObjectBehavior;

class TranslatedColumnPresenterSpec extends ObjectBehavior
{
    function it_only_handles_export_with_translated_header()
    {
        $data = [
            'sku--[sku]',
            'description--Description',
            'name--Nom',
        ];
        $expectedData = [
            'sku--[sku]' => 'sku--[sku]',
            'description--Description' => 'description--Description',
            'name--Nom' => 'name--Nom',
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => false])->shouldReturn($expectedData);
    }

    function it_formats_duplicated_translations()
    {
        $data = [
            'brand--Marque',
            'camera_brand--Marque',
            'size--Taille',
            'objective_size--Taille',
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => true])->shouldReturn([
            'brand--Marque' => 'Marque - brand',
            'camera_brand--Marque' => 'Marque - camera_brand',
            'size--Taille' => 'Taille - size',
            'objective_size--Taille' => 'Taille - objective_size',
        ]);
    }

    function it_removes_code_when_translation_is_not_duplicated()
    {
        $data = [
            'sku--[sku]',
            'description--Description',
            'name--Nom',
            'brand--Marque',
            'camera_brand--Marque',
        ];

        $this->present($data, ['with_label' => true, 'header_with_label' => true])->shouldReturn([
            'sku--[sku]' => '[sku]',
            'description--Description' => 'Description',
            'name--Nom' => 'Nom',
            'brand--Marque' => 'Marque - brand',
            'camera_brand--Marque' => 'Marque - camera_brand',
        ]);
    }
}
