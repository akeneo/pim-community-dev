@javascript
Feature: Export attributes
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export attributes

  Scenario: Successfully export attributes with reference data
    Given a "footwear" catalog configuration
    And the following job "footwear_attribute_export" configuration:
      | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_attribute_export" export job page
    When I launch the export job
    And I wait for the "footwear_attribute_export" job to finish
    Then exported file of "footwear_attribute_export" should contain:
    """
    type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable
    pim_catalog_identifier;sku;SKU;info;1;1;;;;;0;0
    pim_catalog_text;name;Name;info;0;1;;;;;1;0
    pim_catalog_simpleselect;manufacturer;Manufacturer;info;0;1;;;;;0;0
    pim_catalog_multiselect;weather_conditions;"Weather conditions";info;0;1;;;;;0;0
    pim_catalog_textarea;description;Description;info;0;1;;;;;1;1
    pim_catalog_text;comment;Comment;other;0;1;;;;;0;0
    pim_catalog_price_collection;price;Price;marketing;0;1;;;;;0;0
    pim_catalog_simpleselect;rating;Rating;marketing;0;1;;;;;0;0
    pim_catalog_image;side_view;"Side view";media;0;0;gif,png,jpeg,jpg;;;;0;0
    pim_catalog_image;top_view;"Top view";media;0;0;gif,png,jpeg,jpg;;;;0;0
    pim_catalog_simpleselect;size;Size;sizes;0;1;;;;;0;0
    pim_catalog_simpleselect;color;Color;colors;0;1;;;;;0;0
    pim_catalog_simpleselect;lace_color;"Lace color";colors;0;1;;;;;0;0
    pim_catalog_metric;length;Length;info;0;0;;Length;CENTIMETER;;0;0
    pim_catalog_number;number_in_stock;"Number in stock";other;0;0;;;;;0;0
    pim_catalog_date;destocking_date;"Destocking date";other;0;1;;;;;0;0
    pim_catalog_boolean;handmade;Handmade;other;0;1;;;;;0;0
    pim_reference_data_simpleselect;heel_color;Heel color;other;0;1;;;;color;0;0
    pim_reference_data_simpleselect;sole_color;Sole color;other;0;1;;;;color;0;0
    pim_reference_data_simpleselect;cap_color;Cap color;other;0;1;;;;color;1;1
    pim_reference_data_multiselect;sole_fabric;Sole fabric;other;0;1;;;;fabrics;0;0
    pim_reference_data_multiselect;lace_fabric;Lace fabric;other;0;1;;;;fabrics;1;1
    pim_catalog_text;123;"Attribute 123";other;0;1;;;;;0;0
    """
