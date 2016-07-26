@javascript
Feature: Export product by attribute date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given date attribute

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code | requirements-mobile |
      | CD   | sku                 |
    And the following products:
      | sku              | enabled | family | categories      | destocking_date |
      | CD-RATM          | 1       | CD     | 2014_collection | 2016-08-13      |
      | CD-AEROSMITH     | 1       | CD     | 2014_collection | 2015-01-09      |
      | CD-BLACK-SABBATH | 1       | CD     | 2014_collection |                 |

  Scenario: Successfully export products filtered with an empty date attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Destocking date
    And I filter by "destocking_date" with operator "Is empty" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Is empty"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;destocking_date
    CD-BLACK-SABBATH;2014_collection;1;CD;;
    """

  Scenario: Successfully export products filtered with a value greater than a date attribute using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Destocking date
    And I filter by "destocking_date" with operator "Greater than" and value "08/13/2015"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Greater than"
    And the field filter-value-start should contain "08/13/2015"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;destocking_date
    CD-RATM;2014_collection;1;CD;;2016-08-13
    """

  Scenario: Successfully export products filtered by date attribute between two values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Destocking date
    And I filter by "destocking_date" with operator "Between" and value "08/13/2014 and 09/01/2017"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I visit the "Content" tab
    Then I should see the text "Between"
    And the field filter-value-start should contain "08/13/2014"
    And the field filter-value-end should contain "09/01/2017"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;destocking_date
    CD-RATM;2014_collection;1;CD;;2016-08-13
    CD-AEROSMITH;2014_collection;1;CD;;2015-01-09
    """

  Scenario: Successfully export products filtered with a value lower than a date attribute without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                            |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "destocking_date", "operator": "<", "value": "2016-08-13"}]} |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;destocking_date
    CD-AEROSMITH;2014_collection;1;CD;;2015-01-09
    """
