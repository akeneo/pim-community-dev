@javascript
Feature: Export product by attribute number
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given number attribute

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code | requirements-mobile |
      | CD   | sku                 |
    And the following products:
      | sku              | enabled | family | categories      | number_in_stock |
      | CD-RATM          | 1       | CD     | 2014_collection | 17500           |
      | CD-AEROSMITH     | 1       | CD     | 2014_collection | 10000           |
      | CD-BLACK-SABBATH | 1       | CD     | 2014_collection |                 |

  Scenario: Successfully export products filtered with an empty pim_catalog_number attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Number in stock
    And I filter by "number_in_stock" with operator "Is empty" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Is empty"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;number_in_stock
    CD-BLACK-SABBATH;2014_collection;1;CD;;
    """

  Scenario: Successfully export products filtered with a greater than a value of a pim_catalog_number attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Number in stock
    And I filter by "number_in_stock" with operator "Greater than" and value "12000"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Greater than"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;number_in_stock
    CD-RATM;2014_collection;1;CD;;17500
    """

  Scenario: Successfully export products filtered with a between two values of a pim_catalog_number attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Number in stock
    And I filter by "number_in_stock" with operator "Is not empty" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Is not empty"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;number_in_stock
    CD-RATM;2014_collection;1;CD;;17500
    CD-AEROSMITH;2014_collection;1;CD;;10000
    """

  Scenario: Successfully export products filtered with an equal to a value of a pim_catalog_number attribute without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                     |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "number_in_stock", "operator": "=", "value": 10000}]} |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;number_in_stock
    CD-AEROSMITH;2014_collection;1;CD;;10000
    """
