@javascript
Feature: Import variant products
  In order import my variant product
  As a catalog manager
  I need to be able to import product models with valid data

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip a variant product if its family is different than its parent
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;shoes;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "The variant product family must be the same than its parent"