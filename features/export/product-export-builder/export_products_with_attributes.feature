@javascript
Feature: Export products with only selected attributes
  In order to export products with a subset of attributes
  As a product manager
  I need to be able to export only the attributes I need

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | weather_conditions |
      | BOOT-1 | boots  | The boot 1 |                    |
      | BOOT-2 | boots  | The boot 2 | dry                |
    And I am logged in as "Julia"

  Scenario: Export products by selecting only one attribute
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                              |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile", "attributes": ["weather_conditions"]},"data":[]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions
    BOOT-1;;1;boots;;
    BOOT-2;;1;boots;;dry
    """

  Scenario: Export products by selecting only one attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                              |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I select the following attributes to export weather_conditions
    And I press the "Save" button
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions
    BOOT-1;;1;boots;;
    BOOT-2;;1;boots;;dry
    """

  Scenario: Export products by selecting only multiple attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                              |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I select the following attributes to export weather_conditions and lace_color
    And I press the "Save" button
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions;lace_color
    BOOT-1;;1;boots;;;
    BOOT-2;;1;boots;;dry;
    """

  Scenario: Export products by selecting only any attributes using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                              |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I select no attribute to export
    And I press the "Save" button
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    BOOT-1;;;;1;boots;;;;"The boot 1";;;;;;;
    BOOT-2;;;;1;boots;;;;"The boot 2";;;;;;;dry
    """
