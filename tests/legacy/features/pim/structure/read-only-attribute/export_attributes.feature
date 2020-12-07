@javascript
Feature: Display available field options
  In order to create a read only attribute
  As a product manager
  I need to see and manage the option 'Read only'

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"

  Scenario: Successfully export attribute with read only parameter
    And the following attributes:
      | code      | label-en_US | type                 | allowed_extensions | date_min   | max_characters | group | wysiwyg_enabled |
      | info      | Info        | pim_catalog_textarea |                    |            | 25             | other | true            |
    And the following job "csv_clothing_attribute_export" configuration:
      | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I am on the "csv_clothing_attribute_export" export job page
    When I launch the export job
    And I wait for the "csv_clothing_attribute_export" job to finish
    Then exported file of "csv_clothing_attribute_export" should contain:
    """
    code;label-en_US;label-fr_FR;label-de_DE;allowed_extensions;auto_option_sorting;available_locales;date_max;date_min;decimals_allowed;default_metric_unit;group;is_read_only;localizable;max_characters;max_file_size;metric_family;minimum_input_length;negative_allowed;number_max;number_min;reference_data_name;scopable;sort_order;type;unique;useable_as_grid_filter;validation_regexp;validation_rule;wysiwyg_enabled;default_value
    sku;SKU;;;;;;;;;;info;0;0;;;;;;;;;0;1;pim_catalog_identifier;1;1;;;;
    name;Name;Nom;;;;;;;;;info;0;1;;;;;;;;;0;2;pim_catalog_text;0;1;;;;
    manufacturer;Manufacturer;;;;1;;;;;;info;0;0;;;;;;;;;0;3;pim_catalog_simpleselect;0;1;;;;
    weather_conditions;"Weather conditions";;;;;;;;;;info;0;0;;;;;;;;;0;4;pim_catalog_multiselect;0;1;;;;
    description;Description;Description;;;;;;;;;info;0;1;1000;;;;;;;;1;5;pim_catalog_textarea;0;1;;;;
    comment;Comment;;;;;;;;;;other;0;0;255;;;;;;;;0;1;pim_catalog_text;0;1;;;;
    price;Price;;;;;;;;1;;marketing;0;0;;;;;;200.0000;1.0000;;0;1;pim_catalog_price_collection;0;1;;;;
    rating;Rating;;;;;;;;;;marketing;0;0;;;;;;;;;0;2;pim_catalog_simpleselect;0;1;;;;
    side_view;"Side view";;;gif,png,jpeg,jpg;;;;;;;media;0;0;;1.00;;;;;;;0;1;pim_catalog_image;0;0;;;;
    top_view;"Top view";;;gif,png,jpeg,jpg;;;;;;;media;0;0;;1.00;;;;;;;0;2;pim_catalog_image;0;0;;;;
    datasheet;Datasheet;;;txt,pdf,doc,docx,csv,rtf;;;;;;;media;0;0;;;;;;;;;0;3;pim_catalog_file;0;0;;;;
    size;Size;;;;;;;;;;sizes;0;0;;;;;;;;;0;1;pim_catalog_simpleselect;0;1;;;;
    main_color;"Main color";;;;;;;;;;colors;0;0;;;;;;;;;0;1;pim_catalog_simpleselect;0;1;;;;
    secondary_color;"Secondary color";;;;;;;;;;colors;0;0;;;;;;;;;0;2;pim_catalog_simpleselect;0;1;;;;
    length;Length;;;;;;;;0;CENTIMETER;sizes;0;0;;;Length;;0;;;;0;10;pim_catalog_metric;0;0;;;;
    width;Width;;;;;;;;0;CENTIMETER;sizes;0;0;;;Length;;0;;;;0;10;pim_catalog_metric;0;0;;;;
    number_in_stock;"Number in stock";"Nombre en stock";"Anzahl auf Lager";;;;;;0;;marketing;0;0;;;;;0;10000.0000;1.0000;;1;2;pim_catalog_number;0;1;;;;
    handmade;Handmade;"Fait main";Handgefertigt;;;;;;;;info;0;0;;;;;;;;;0;3;pim_catalog_boolean;0;1;;;;
    release_date;"Release date";"Date de sortie";Erscheinungsdatum;;;;;;;;info;0;0;;;;;;;;;1;3;pim_catalog_date;0;1;;;;
    legacy_attribute;"Old attribute not used anymore";;;;;;;;;;legacy;0;0;;;;;;;;;0;30;pim_catalog_text;0;0;;;;
    lace_color;"Lace color";;;;;;;;;;other;0;0;;;;;;;;color;0;30;pim_reference_data_simpleselect;0;1;;;;
    sleeve_color;"Sleeve color";;;;;;;;;;other;0;0;;;;;;;;color;0;35;pim_reference_data_simpleselect;0;1;;;;
    zip_color;"Zip color";;;;;;;;;;other;0;1;;;;;;;;color;1;35;pim_reference_data_simpleselect;0;1;;;;
    sleeve_fabric;"Sleeve fabric";;;;;;;;;;other;0;0;;;;;;;;fabrics;0;40;pim_reference_data_multiselect;0;1;;;;
    neck_fabric;"Neck fabric";;;;;;;;;;other;0;1;;;;;;;;fabrics;1;45;pim_reference_data_multiselect;0;1;;;;
    volume;Volume;;;;;;;;0;CUBIC_MILLIMETER;info;0;0;;;Volume;;0;;;;0;55;pim_catalog_metric;0;1;;;;
    info;Info;;;;;;;;;;other;0;0;25;;;;;;;;0;0;pim_catalog_textarea;0;0;;;1;
    """
