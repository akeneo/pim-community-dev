@javascript
Feature: Export attributes
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export attributes

  @ce @critical
  Scenario: Successfully export attributes with reference data
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_attribute_export" configuration:
      | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_attribute_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_attribute_export" job to finish
    Then exported file of "csv_footwear_attribute_export" should contain:
    """
    type;code;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;auto_option_sorting;metric_family;default_metric_unit;reference_data_name;available_locales;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;max_file_size;minimum_input_length;localizable;scopable;sort_order
    pim_catalog_identifier;sku;SKU;;info;1;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_text;name;Name;;info;0;1;;;;;;;0;;;;;;;;;;;0;1;0;0
    pim_catalog_simpleselect;manufacturer;Manufacturer;;info;0;1;;1;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_multiselect;weather_conditions;"Weather conditions";;info;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_textarea;description;Description;;info;0;1;;;;;;;1000;;;;;;;;;;;0;1;1;0
    pim_catalog_text;comment;Comment;;other;0;1;;;;;;;255;;;;;;;;;;;0;0;0;0
    pim_catalog_price_collection;price;Price;;marketing;0;1;;;;;;;0;;;;1.0000;200.0000;1;;;;;0;0;0;0
    pim_catalog_simpleselect;rating;Rating;;marketing;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_image;side_view;"Side view";;media;0;0;gif,png,jpeg,jpg;;;;;;0;;;;;;;;;;1.00;0;0;0;0
    pim_catalog_image;top_view;"Top view";;media;0;0;gif,png,jpeg,jpg;;;;;;0;;;;;;;;;;1.00;0;0;0;0
    pim_catalog_simpleselect;size;Size;;sizes;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_simpleselect;color;Color;;colors;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_simpleselect;lace_color;"Lace color";;colors;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_metric;length;Length;;info;0;0;;;Length;CENTIMETER;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_metric;volume;Volume;;info;0;0;;;Volume;CUBIC_MILLIMETER;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_number;number_in_stock;"Number in stock";;other;0;0;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_date;destocking_date;"Destocking date";;other;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_catalog_boolean;handmade;Handmade;;other;0;1;;;;;;;0;;;;;;;;;;;0;0;0;0
    pim_reference_data_simpleselect;heel_color;"Heel color";;other;0;1;;;;;color;;0;;;;;;;;;;;0;0;0;0
    pim_reference_data_simpleselect;sole_color;"Sole color";;other;0;1;;;;;color;;0;;;;;;;;;;;0;0;0;0
    pim_reference_data_simpleselect;cap_color;"Cap color";;other;0;1;;;;;color;;0;;;;;;;;;;;0;1;1;0
    pim_reference_data_multiselect;sole_fabric;"Sole fabric";;other;0;1;;;;;fabrics;;0;;;;;;;;;;;0;0;0;0
    pim_reference_data_multiselect;lace_fabric;"Lace fabric";;other;0;1;;;;;fabrics;;0;;;;;;;;;;;0;1;1;0
    pim_catalog_number;rate_sale;"Rate of sale";;marketing;0;1;;;;;;;0;;;;;;1;;;;;0;0;0;0
    pim_catalog_metric;weight;Weight;;info;0;1;;;Weight;GRAM;;;0;;;;;;1;;;;;0;0;0;0
    pim_catalog_text;123;"Attribute 123";;other;0;1;;;;;;;255;;;;;;;;;;;0;0;0;0
    pim_catalog_image;rear_view;Rear view;media;0;0;gif,png,jpeg,jpg;;;;;1;1;0;2;;;;;;;1
    """
