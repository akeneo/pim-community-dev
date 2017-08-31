@javascript
Feature: Import variant products
  In order to setup my application
  As a product manager
  I need to be able to import new product models

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Import variant product
    Given the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon_blue;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then the parent of the product "apollon_blue_medium" should be "apollon_blue"
    And the product "apollon_blue_medium" should have the following values:
      | ean    | EAN                 |
      | sku    | apollon_blue_medium |
      | weight | 100.0000 GRAM       |
      | size   | [m]                 |

  Scenario: When we import a variant product without a family, then its parent's family is assigned to it.
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;categories;ean;sku;weight;weight-unit;size
      code-002;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    And the family of "SKU-001" should be "clothing"

  Scenario: Import variant product by ignoring attributes that are not part of the family
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size;color
      code-002;clothing;master_men;EAN;SKU-001;100;GRAM;m;red
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then the variant product "SKU-001" should not have the following values:
      | color |
