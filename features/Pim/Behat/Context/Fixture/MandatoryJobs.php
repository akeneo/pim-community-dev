<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Fixture;

use Symfony\Component\Yaml\Parser;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MandatoryJobs
{
    private const CONFIGURATIONS = <<<YAML
add_product_value:
    connector: Akeneo Mass Edit Connector
    alias:     add_product_value
    label:     Mass add products values
    type:      mass_edit
update_product_value:
    connector: Akeneo Mass Edit Connector
    alias:     update_product_value
    label:     Mass update products
    type:      mass_edit
remove_product_value:
    connector: Akeneo Mass Edit Connector
    alias:     remove_product_value
    label:     Mass remove products values
    type:      mass_edit
move_to_category:
    connector: Akeneo Mass Edit Connector
    alias:     move_to_category
    label:     Mass move to categories
    type:      mass_edit
add_association:
    connector: Akeneo Mass Edit Connector
    alias:     add_association
    label:     Mass associate products
    type:      mass_edit
add_to_category:
    connector: Akeneo Mass Edit Connector
    alias:     add_to_category
    label:     Mass add to categories
    type:      mass_edit
remove_from_category:
    connector: Akeneo Mass Edit Connector
    alias:     remove_from_category
    label:     Mass remove from categories
    type:      mass_edit
edit_common_attributes:
    connector: Akeneo Mass Edit Connector
    alias:     edit_common_attributes
    label:     Mass edit product attributes
    type:      mass_edit
set_attribute_requirements:
    connector: Akeneo Mass Edit Connector
    alias:     set_attribute_requirements
    label:     Set family attribute requirements
    type:      mass_edit
add_to_existing_product_model:
    connector: Akeneo Mass Edit Connector
    alias:     add_to_existing_product_model
    label:     Add to existing product model
    type:      mass_edit
csv_product_quick_export:
    connector: Akeneo CSV Connector
    alias: csv_product_quick_export
    label: CSV product quick export
    type: quick_export
    configuration:
        delimiter:  ;
        enclosure:  '"'
        withHeader: true
        filePathProduct:      /tmp/1_products-quick-export.csv
        filePathProductModel: /tmp/2_product-models-quick-export.csv
csv_product_grid_context_quick_export:
    connector: Akeneo CSV Connector
    alias: csv_product_grid_context_quick_export
    label: CSV product quick export grid context
    type: quick_export
    configuration:
        delimiter:  ;
        enclosure:  '"'
        withHeader: true
        filePathProduct:      /tmp/1_products_export_grid_context_%locale%_%scope%.csv
        filePathProductModel: /tmp/2_product_models_export_grid_context_%locale%_%scope%.csv
xlsx_product_quick_export:
    connector: Akeneo XLSX Connector
    alias: xlsx_product_quick_export
    label: XLSX product quick export
    type: quick_export
    configuration:
        withHeader: true
        linesPerFile: 10000
        filePathProduct:      /tmp/1_products_export_%locale%_%scope%.xlsx
        filePathProductModel: /tmp/2_product_models_export_%locale%_%scope%.xlsx
xlsx_product_grid_context_quick_export:
    connector: Akeneo XLSX Connector
    alias: xlsx_product_grid_context_quick_export
    label: XLSX product quick export grid context
    type: quick_export
    configuration:
        withHeader: true
        linesPerFile: 10000
        filePathProduct:      /tmp/1_products_export_grid_context_%locale%_%scope%.xlsx
        filePathProductModel: /tmp/2_product_models_export_grid_context_%locale%_%scope%.xlsx
csv_default_product_import:
    connector: Akeneo CSV Connector
    alias:     csv_product_import
    label:     CSV default product import
    type:      import
compute_product_models_descendants:
    connector: internal
    alias:     compute_product_models_descendants
    label:     Compute product models descendants
    type:      compute_product_models_descendants
compute_completeness_of_products_family:
    connector: internal
    alias:     compute_completeness_of_products_family
    label:     compute completeness of products family
    type:      compute_completeness_of_products_family
compute_family_variant_structure_changes:
    connector: internal
    alias:     compute_family_variant_structure_changes
    label:     Compute variant structure changes
    type:      compute_family_variant_structure_changes
YAML;

    /**
     * @return array
     */
    public static function getConfigurations(): array
    {
        $parser = new Parser();
        $configurations = $parser->parse(static::CONFIGURATIONS);

        foreach (array_keys($configurations) as $key) {
            $configurations[$key]['code'] = $key;
        }

        return $configurations;
    }
}
