@javascript
Feature: Import products with rules
  In order ease the enrichment of the catalog
  As a regular user
  I need to be able to import products and apply rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following product rule definitions:
      """
      set_tees_description:
        priority: 10
        conditions:
          - field:    categories.code
            operator: IN
            value:
              - tees
        actions:
          - type:  set
            field: description
            value: an other description
            locale: fr_FR
            scope: tablet
      """

  Scenario: Successfully update product and apply rule
    Given the following CSV file to import:
      """
      sku;family;categories;description-fr_FR-tablet
      SKU-001;tees;tees;a description
      """
    And the following job "csv_clothing_product_import_with_rules" configuration:
      | filePath | %file to import% |
    And the following products:
      | sku     | family | categories | description-fr_FR-tablet |
      | SKU-001 | tees   | tees       | a description            |
    Given I am on the "csv_clothing_product_import_with_rules" import job page
    When I launch the import job
    And I wait for the "csv_clothing_product_import_with_rules" job to finish
    Then there should be 1 product
    And the french tablet description of "SKU-001" should be "an other description"

  Scenario: Successfully add new product and apply rule with CSV
    Given the following CSV file to import:
      """
      sku;family;categories;description-fr_FR-tablet
      SKU-001;tees;tees;a description
      """
    And the following job "csv_clothing_product_import_with_rules" configuration:
      | filePath | %file to import% |
    And I am on the "csv_clothing_product_import_with_rules" import job page
    When I launch the import job
    And I wait for the "csv_clothing_product_import_with_rules" job to finish
    Then there should be 1 product
    And the french tablet description of "SKU-001" should be "an other description"

  Scenario: Successfully add new product and apply rule with XLSX
    Given the following XLSX file to import:
      """
      sku;family;categories;description-fr_FR-tablet
      SKU-001;tees;tees;a description
      """
    And the following job "xlsx_clothing_product_import_with_rules" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_clothing_product_import_with_rules" import job page
    And I launch the import job
    And I wait for the "xlsx_clothing_product_import_with_rules" job to finish
    Then there should be 1 product
    And the french tablet description of "SKU-001" should be "an other description"
