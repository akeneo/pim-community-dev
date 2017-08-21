@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Import product without optional attributes, they are ignored
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;material;eu_shoes_size
      code-001;clothing;master_men;ean;sku;metal;42
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then the product "sku" should not have the following values:
      | comment |

