@javascript
Feature: Export product by attribute date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products from any given date attribute

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code          | type              | label-en_US   | group |
      | delivery_date | pim_catalog_date  | Delivery date | other |
      | purchase_date | pim_catalog_date  | Purchase date | other |
    And the following family:
      | code | requirements-mobile | attributes                                    |
      | CD   | sku                 | destocking_date,delivery_date,purchase_date   |
    And the following products:
      | sku              | enabled | family | categories      | destocking_date | delivery_date | purchase_date |
      | CD-RATM          | 1       | CD     | 2014_collection | 2016-08-13      |  2015-09-17   |               |
      | CD-AEROSMITH     | 1       | CD     | 2014_collection | 2015-01-09      |  2013-06-15   |               |
      | CD-BLACK-SABBATH | 1       | CD     | 2014_collection | 2016-08-13      |  2030-07-12   | 2010-03-18    |

  Scenario: Successfully export products filtered with empty, greater than and between filters on date attributes
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I visit the "Content" tab
    And I add available attributes Purchase date
    And I add available attributes Destocking date
    And I add available attributes Delivery date
    And I filter by "purchase_date" with operator "Is empty" and value ""
    And I filter by "destocking_date" with operator "Greater than" and value "08/13/2015"
    And I filter by "delivery_date" with operator "Between" and value "09/13/2015 and 09/01/2017"
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
    sku;categories;enabled;family;groups;delivery_date;destocking_date;purchase_date
    CD-RATM;2014_collection;1;CD;;2015-09-17;2016-08-13;
    """
