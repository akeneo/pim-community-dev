@javascript
Feature: Display available field options
  In order to create a read only attribute
  As a product manager
  I need to see and manage the option 'Is read only'

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"

  Scenario: Successfully export attribute with read only parameter
    And the following job "csv_clothing_attribute_export" configuration:
    | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I am on the "csv_clothing_attribute_export" export job page
    When I launch the export job
    And I wait for the "csv_clothing_attribute_export" job to finish
    Then exported file of "csv_clothing_attribute_export" should contain:
    """
    type;allowed_extensions;available_locales;code;date_max;date_min;decimals_allowed;default_metric_unit;group;is_read_only;label-de_DE;label-en_GB;label-en_US;label-fr_FR;localizable;max_characters;max_file_size;metric_family;minimum_input_length;negative_allowed;number_max;number_min;reference_data_name;scopable;unique;useable_as_grid_filter;validation_regexp;validation_rule;wysiwyg_enabled
    pim_catalog_identifier;;All;sku;;;;;info;0;;;SKU;;0;0;;;0;;;;;0;1;1;;;
    pim_catalog_text;;All;name;;;;;info;0;;;Name;Nom;1;0;;;0;;;;;0;0;1;;;
    pim_catalog_simpleselect;;All;manufacturer;;;;;info;0;;;Manufacturer;;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_multiselect;;All;weather_conditions;;;;;info;0;;;"Weather conditions";;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_textarea;;All;description;;;;;info;0;;;Description;Description;1;1000;;;0;;;;;1;0;1;;;
    pim_catalog_text;;All;comment;;;;;other;0;;;Comment;;0;255;;;0;;;;;0;0;1;;;
    pim_catalog_price_collection;;All;price;;;1;;marketing;0;;;Price;;0;0;;;0;;200.0000;1.0000;;0;0;1;;;
    pim_catalog_simpleselect;;All;rating;;;;;marketing;0;;;Rating;;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_image;gif,png,jpeg,jpg;All;side_view;;;;;media;0;;;"Side view";;0;0;1.00;;0;;;;;0;0;0;;;
    pim_catalog_image;gif,png,jpeg,jpg;All;top_view;;;;;media;0;;;"Top view";;0;0;1.00;;0;;;;;0;0;0;;;
    pim_catalog_file;avi;All;video;;;;;media;0;;;Video;;0;0;1.00;;0;;;;;0;0;0;;;
    pim_catalog_file;txt,pdf,doc,docx,csv,rtf;All;datasheet;;;;;media;0;;;Datasheet;;0;0;;;0;;;;;0;0;0;;;
    pim_catalog_simpleselect;;All;size;;;;;sizes;0;;;Size;;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_simpleselect;;All;main_color;;;;;colors;0;;;"Main color";;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_simpleselect;;All;secondary_color;;;;;colors;0;;;"Secondary color";;0;0;;;0;;;;;0;0;1;;;
    pim_catalog_metric;;All;length;;;;CENTIMETER;sizes;0;;;Length;;0;0;;Length;0;;;;;0;0;0;;;
    pim_catalog_metric;;All;width;;;;CENTIMETER;sizes;0;;;Width;;0;0;;Length;0;;;;;0;0;0;;;
    pim_catalog_number;;All;number_in_stock;;;;;marketing;0;"Anzahl auf Lager";"Number in stock";"Number in stock";"Nombre en stock";0;0;;;0;;10000.0000;1.0000;;1;0;1;;;
    pim_catalog_boolean;;All;handmade;;;;;info;0;Handgefertigt;Handmade;Handmade;"Fait main";0;0;;;0;;;;;0;0;1;;;
    pim_catalog_date;;All;release_date;;;;;info;0;Erscheinungsdatum;"Release date";"Release date";"Date de sortie";0;0;;;0;;;;;1;0;1;;;
    pim_catalog_text;;All;legacy_attribute;;;;;legacy;0;;;"Old attribute not used anymore";;0;0;;;0;;;;;0;0;0;;;
    pim_reference_data_simpleselect;;All;lace_color;;;;;other;0;;;"Lace color";;0;0;;;0;;;;color;0;0;1;;;
    pim_reference_data_simpleselect;;All;sleeve_color;;;;;other;0;;;"Sleeve color";;0;0;;;0;;;;color;0;0;1;;;
    pim_reference_data_simpleselect;;All;zip_color;;;;;other;0;;;"Zip color";;1;0;;;0;;;;color;1;0;1;;;
    pim_reference_data_multiselect;;All;sleeve_fabric;;;;;other;0;;;"Sleeve fabric";;0;0;;;0;;;;fabrics;0;0;1;;;
    pim_reference_data_multiselect;;All;neck_fabric;;;;;other;0;;;"Neck fabric";;1;0;;;0;;;;fabrics;1;0;1;;;
    pim_assets_collection;;All;front_view;;;;;media;0;Vorderansicht;;"Front view";"Vue de face";0;0;;;0;;;;assets;0;0;1;;;
    pim_assets_collection;;All;gallery;;;;;media;0;;;gallery;;0;0;;;0;;;;assets;0;0;0;;;
    pim_catalog_metric;;All;volume;;;;CUBIC_MILLIMETER;info;0;;;Volume;;0;0;;Volume;0;;;;;0;0;1;;;
    """
