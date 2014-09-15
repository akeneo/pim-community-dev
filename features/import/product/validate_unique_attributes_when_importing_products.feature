@javascript
Feature: Validate values for unique attributes when importing products
  In order to keep catalog data consistent
  As a product manager
  I need to be sure that unique constraints are applied during product import

  Background:
    Given an "apparel" catalog configuration
    And the following attributes:
      | code   | type   | unique |
      | date   | date   | yes    |
      | number | number | yes    |
      | text   | text   | yes    |
    And I am logged in as "Julia"

  Scenario: Successfully ignore duplicate unique date values
    Given the following file to import:
      """
      sku;date
      SKU-001;2014-01-01
      SKU-002;2014-01-01
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"2014-01-01T00:00:00+0100\" for unique attribute \"date\" was already read in this file"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique number values
    Given the following file to import:
      """
      sku;number
      SKU-001;123
      SKU-002;123
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"123\" for unique attribute \"number\" was already read in this file"
    And there should be 1 product

  Scenario: Successfully ignore duplicate unique text values
    Given the following file to import:
      """
      sku;text
      SKU-001;foo
      SKU-002;foo
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "The value \"foo\" for unique attribute \"text\" was already read in this file"
    And there should be 1 product
