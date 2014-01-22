@javascript
Feature: Export attributes
  In order to be able to access and modify attributes data outside PIM
  As Julia
  I need to be able to export attributes

  Scenario: Successfully export attributes
    Given a "footwear" catalog configuration
    And the following job "footwear_attribute_export" configuration:
      | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_attribute_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then exported file of "footwear_attribute_export" should contain:
    """
    type;code;label-en_US;group;unique;useable_as_grid_column;useable_as_grid_filter;allowed_extensions;date_type;metric_family;default_metric_unit;translatable;scopable
    pim_catalog_identifier;sku;SKU;info;1;1;1;;;;;0;0
    pim_catalog_text;name;Name;info;0;1;1;;;;;1;0
    pim_catalog_simpleselect;manufacturer;Manufacturer;info;0;0;1;;;;;0;0
    pim_catalog_multiselect;weather_conditions;"Weather conditions";info;0;0;1;;;;;0;0
    pim_catalog_textarea;description;Description;info;0;0;1;;;;;1;1
    pim_catalog_price_collection;price;Price;marketing;0;1;1;;;;;0;0
    pim_catalog_simpleselect;rating;Rating;marketing;0;1;1;;;;;0;0
    pim_catalog_image;side_view;"Side view";media;0;0;0;gif,png,jpeg;;;;0;0
    pim_catalog_image;top_view;"Top view";media;0;0;0;gif,png,jpeg;;;;0;0
    pim_catalog_simpleselect;size;Size;sizes;0;1;1;;;;;0;0
    pim_catalog_simpleselect;color;Color;colors;0;1;1;;;;;0;0
    pim_catalog_simpleselect;lace_color;"Lace color";colors;0;0;1;;;;;0;0
    pim_catalog_metric;length;Length;info;0;0;0;;;Length;CENTIMETER;0;0

    """

  Scenario: Successfully export all label locales even if no value were set
    Given a "footwear" catalog configuration
    And the following job "footwear_attribute_export" configuration:
      | filePath | %tmp%/attribute_export/attribute_export.csv |
    And I add the "fr_BE" locale to the "tablet" channel
    And I am logged in as "Julia"
    And I am on the "footwear_attribute_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then exported file of "footwear_attribute_export" should contain:
    """
    type;code;label-en_US;label-fr_BE;group;unique;useable_as_grid_column;useable_as_grid_filter;allowed_extensions;date_type;metric_family;default_metric_unit;translatable;scopable
    pim_catalog_identifier;sku;SKU;;info;1;1;1;;;;;0;0
    pim_catalog_text;name;Name;;info;0;1;1;;;;;1;0
    pim_catalog_simpleselect;manufacturer;Manufacturer;;info;0;0;1;;;;;0;0
    pim_catalog_multiselect;weather_conditions;"Weather conditions";;info;0;0;1;;;;;0;0
    pim_catalog_textarea;description;Description;;info;0;0;1;;;;;1;1
    pim_catalog_price_collection;price;Price;;marketing;0;1;1;;;;;0;0
    pim_catalog_simpleselect;rating;Rating;;marketing;0;1;1;;;;;0;0
    pim_catalog_image;side_view;"Side view";;media;0;0;0;gif,png,jpeg;;;;0;0
    pim_catalog_image;top_view;"Top view";;media;0;0;0;gif,png,jpeg;;;;0;0
    pim_catalog_simpleselect;size;Size;;sizes;0;1;1;;;;;0;0
    pim_catalog_simpleselect;color;Color;;colors;0;1;1;;;;;0;0
    pim_catalog_simpleselect;lace_color;"Lace color";;colors;0;0;1;;;;;0;0
    pim_catalog_metric;length;Length;;info;0;0;0;;;Length;CENTIMETER;0;0

    """
