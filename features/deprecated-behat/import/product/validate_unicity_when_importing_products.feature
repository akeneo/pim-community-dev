@deprecated @javascript
Feature: Validate values for unique attributes when importing products
  In order to keep catalog data consistent
  As a product manager
  I need to be sure that unique constraints are applied during product import

  Background:
    Given an "footwear" catalog configuration
    And the following attributes:
      | code                  | type   | group     | unique | scopable | localizable | useable_as_grid_filter |
      | test_unique_attribute | text   | marketing | yes    | no       | no          | yes                    |
      | date                  | date   | marketing | yes    | no       | no          | yes                    |
      | number                | number | marketing | yes    | no       | no          | yes                    |
      | text                  | text   | marketing | yes    | no       | no          | yes                    |
    And I am logged in as "Julia"

  Scenario: Successfully ignore duplicate unique date values
    Given the following CSV file to import:
      """
      sku;date
      SKU-001;2014-01-01
      SKU-002;2014-01-01
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"2014-01-01T00:00:00+0100\" for unique attribute \"date\" was already read in this file"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique number values
    Given the following CSV file to import:
      """
      sku;number
      SKU-001;123
      SKU-002;123
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"123\" for unique attribute \"number\" was already read in this file"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique text values
    Given the following CSV file to import:
      """
      sku;text
      SKU-001;foo
      SKU-002;foo
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"foo\" for unique attribute \"text\" was already read in this file"
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
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "The value \"1200000011a\" for unique attribute \"test_unique_attribute\" was already read in this file"
    And I am on the products page
    When I show the filter "test_unique_attribute"
    And I filter by "test_unique_attribute" with value "1200000011a"
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
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "The unique code \"17727158\" was already read in this file"
    And I am on the products page
    And I filter by "SKU" with value "17727158"
    And I should see products 17727158
    And the grid should contain 1 elements
