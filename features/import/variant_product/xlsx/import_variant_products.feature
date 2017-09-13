@javascript
Feature: Import variant products through XLSX import
  In order to setup my application
  As a product manager
  I need to be able to import variant products

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Create new variant products through XLSX import
    Given the following XLSX file to import:
      """
      sku;color;size;family;parent;name-en_US;weight;weight-unit;ean
      apollon_blue_xl;blue;xl;clothing;apollon_blue;;800;GRAM;EAN
      """
    And the following job "xlsx_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_product_import" job to finish
    Then the parent of the product "apollon_blue_xl" should be "apollon_blue"
    And product "apollon_blue_xl" should be enabled
    And the family of "apollon_blue_xl" should be "clothing"
    And the english localizable value name of "apollon_blue_xl" should be "Long gray suit jacket and matching pants unstructured"
    And the product "apollon_blue_xl" should have the following values:
      | color  | [blue]        |
      | size   | [xl]          |
      | weight | 800.0000 GRAM |
      | ean    | EAN           |

  Scenario: Update values of existing variant products through XLSX import
    Given the following XLSX file to import:
      """
      sku;color;size;family;parent;name-en_US;weight;weight-unit;ean;enabled
      1111111121;blue;s;clothing;apollon_blue;;600;GRAM;EAN;0
      """
    And the following job "xlsx_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_product_import" job to finish
    Then product "1111111121" should be disabled
    And the product "1111111121" should have the following values:
      | color  | [blue]        |
      | size   | [s]           |
      | weight | 600.0000 GRAM |
      | ean    | EAN           |
