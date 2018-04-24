@javascript
Feature: Validate values for unique attributes when importing products
  In order to keep catalog data consistent
  As a product manager
  I need to be sure that unique constraints are applied during product import

  Background:
    Given an "footwear" catalog configuration
    And the following attributes:
      | code                  | type               | group     | unique | scopable | localizable | useable_as_grid_filter | decimals_allowed | negative_allowed |
      | test_unique_attribute | pim_catalog_text   | marketing | 1      | 0        | 0           | 1                      |                  |                  |
      | date                  | pim_catalog_date   | marketing | 1      | 0        | 0           | 1                      |                  |                  |
      | number                | pim_catalog_number | marketing | 1      | 0        | 0           | 1                      | 0                | 0                |
      | text                  | pim_catalog_text   | marketing | 1      | 0        | 0           | 1                      |                  |                  |
    And I am logged in as "Julia"

  Scenario: Successfully ignore duplicate unique date values
    Given the following CSV file to import:
      """
      sku;date
      SKU-001;2014-01-01
      SKU-002;2014-01-01
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The value 2014-01-01 is already set on another product for the unique attribute date"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique number values
    Given the following CSV file to import:
      """
      sku;number
      SKU-001;123
      SKU-002;123
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The value 123 is already set on another product for the unique attribute number"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique text values
    Given the following CSV file to import:
      """
      sku;text
      SKU-001;foo
      SKU-002;foo
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The value foo is already set on another product for the unique attribute text"
    And there should be 1 product

  @jira https://akeneo.atlassian.net/browse/PIM-3309
  Scenario: Import a file with same value in unique attribute and with existing product with this value
    Given the following products:
      | sku         | test_unique_attribute |
      | 17727158    | test                  |
      | AKNTS_BPXL  | 1200000011a           |
      | AKNTS_BPXXL | test2                 |
    And the following CSV file to import:
      """
      sku;test_unique_attribute
      17727158;1200000011a
      AKNTS_BPXL;1200000011a
      AKNTS_BPXXL;1200000011a
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The value 1200000011a is already set on another product for the unique attribute test_unique_attribute"
    And I am on the products grid
    When I show the filter "test_unique_attribute"
    And I filter by "test_unique_attribute" with operator "is equal to" and value "1200000011a"
    And I should see products "AKNTS_BPXL"
    And I should not see products "17727158"
    And I should not see products "AKNTS_BPXXL"
    And the grid should contain 1 elements

  Scenario: Import a file with same sku, duplicated products should not be imported
    Given the following CSV file to import:
      """
      sku
      17727158
      17727158
      17727158
      AKNTS_BPXXL
      """
    And the following products:
      | sku      |
      | 17727158 |
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I am on the products grid
    And I filter by "sku" with operator "is equal to" and value "17727158"
    And I should see products 17727158
    And the grid should contain 1 elements
