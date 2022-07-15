@javascript
Feature: Export products with only selected attributes
  In order to export products with a subset of attributes
  As a product manager
  I need to be able to export only the attributes I need

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code                 | label-en_US          | type                     | group  |
      | high_heel_color      | High heel color      | pim_catalog_simpleselect | colors |
      | lace                 | Lace                 | pim_catalog_text         | colors |
      | high_heel_color_sole | High heel color sole | pim_catalog_simpleselect | colors |
    And the following "high_heel_color" attribute options: Red, Blue
    And the following "high_heel_color_sole" attribute options: Green, Orange
    And the following products:
      | sku    | family | name-en_US | weather_conditions | categories      |
      | BOOT-1 | boots  | The boot 1 |                    | 2014_collection |
      | BOOT-2 | boots  | The boot 2 | dry                | 2014_collection |
    And I am logged in as "Julia"

  Scenario: Export products by selecting multiple attribute in a specific order
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I visit the "Content" tab
    And I select the following attributes to export lace_color and weather_conditions
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;lace_color;weather_conditions
    BOOT-1;;1;boots;;;
    BOOT-2;;1;boots;;;dry
    """
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I visit the "Content" tab
    And I select the following attributes to export weather_conditions and lace_color
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;weather_conditions;lace_color
    BOOT-1;;1;boots;;;
    BOOT-2;;1;boots;;dry;
    """
