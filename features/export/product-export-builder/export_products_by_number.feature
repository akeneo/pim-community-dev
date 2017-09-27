@javascript
Feature: Export product by attribute number
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given number attribute

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code         | type               | label-en_US  | group | decimals_allowed | negative_allowed |
      | order_number | pim_catalog_number | Order number | other | 0                | 0                |
      | track_number | pim_catalog_number | Track number | other | 0                | 0                |
    And the following family:
      | code | requirements-mobile | attributes                                |
      | CD   | sku                 | number_in_stock,order_number,track_number |
    And the following products:
      | sku              | enabled | family | categories      | number_in_stock | order_number | track_number |
      | CD-RATM          | 1       | CD     | 2014_collection | 17500           | 4            |              |
      | CD-AEROSMITH     | 1       | CD     | 2014_collection | 10000           | 6            | 16           |
      | CD-BLACK-SABBATH | 1       | CD     | 2014_collection |                 | 1001         | 10           |

  Scenario: Successfully export products filtered with empty, greater than and not empty filters on pim_catalog_number attribute
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I visit the "Content" tab
    And I add available attributes Number in stock
    And I add available attributes Order number
    And I add available attributes Track number
    And I filter by "number_in_stock" with operator "Is empty" and value ""
    And I filter by "order_number" with operator "Greater than" and value "1000"
    And I filter by "track_number" with operator "Is not empty" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I press the "Edit" button
    When I visit the "Content" tab
    Then I should see the text "Is empty"
    And I move backward one page
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;number_in_stock;order_number;track_number
    CD-BLACK-SABBATH;2014_collection;1;CD;;;1001;10
    """
