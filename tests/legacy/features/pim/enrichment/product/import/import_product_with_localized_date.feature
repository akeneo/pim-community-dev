@javascript
Feature: Import product information with date
  In order to use existing product information
  As a product manager
  I need to be able to import localized products

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file (with date as dd/mm/yyyy) with a date attribute
    Given the following CSV file to import:
      """
      sku;destocking_date;name-en_US
      SKU-001;28/10/2014;sku
      SKU-002;;sku
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath   | %file to import% |
      | dateFormat | dd/MM/yyyy       |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 2 products
    Then the product "SKU-001" should have the following values:
      | destocking_date | 2014-10-28 |
    Then the product "SKU-002" should have the following values:
      | destocking_date |  |

  Scenario: Skip product with a decimal separator different as configuration
    Given the following CSV file to import:
      """
      sku;destocking_date;name-en_US
      SKU-001;28/10/2014;
      SKU-002;28-10-2014;
      SKU-003;2014/10/28;
      SKU-004;2014-10-28;
      SKU-005;;sku
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath   | %file to import% |
      | dateFormat | yyyy-MM-dd       |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 2 products
    Then I should see the text "skipped 3"
    Then the product "SKU-004" should have the following values:
      | destocking_date | 2014-10-28 |
    Then the product "SKU-005" should have the following values:
      | destocking_date |  |
    And I should see the text "This type of value expects the use of the format yyyy-MM-dd for dates."
