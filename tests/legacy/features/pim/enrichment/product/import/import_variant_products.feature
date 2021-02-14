Feature: Import variant products through CSV import
  In order to setup my application
  As a product manager
  I need to be able to import variant products

  Background:
    Given the "catalog_modeling" catalog configuration

  @purge-messenger
  Scenario: Create new variant products through CSV import
    Given the following CSV file to import:
      """
      sku;color;size;family;parent;name-en_US;weight;weight-unit;ean
      apollon_blue_xl;blue;xl;clothing;apollon_blue;;800;GRAM;EAN
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the parent of the product "apollon_blue_xl" should be "apollon_blue"
    And product "apollon_blue_xl" should be enabled
    And the family of "apollon_blue_xl" should be "clothing"
    And the english localizable value name of "apollon_blue_xl" should be "Long gray suit jacket and matching pants unstructured"
    And the product "apollon_blue_xl" should have the following values:
      | color  | blue          |
      | size   | xl            |
      | weight | 800.0000 GRAM |
      | ean    | EAN           |
    And 1 event of type "product.created" should have been raised

  Scenario: Update values of existing variant products through CSV import
    Given the following CSV file to import:
      """
      sku;color;size;family;parent;name-en_US;weight;weight-unit;ean;enabled
      1111111121;blue;s;clothing;apollon_blue;;600;GRAM;EAN;0
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then product "1111111121" should be disabled
    And the product "1111111121" should have the following values:
      | color  | blue          |
      | size   | s             |
      | weight | 600.0000 GRAM |
      | ean    | EAN           |

  Scenario Outline: Convert a variant product to a simple product via import or not, depending on the convertVariantToSimple job parameter
    Given the following job "csv_catalog_modeling_product_import" configuration:
      | filePath               | %file to import%         |
      | enabledComparison      | <enabledComparison>      |
      | convertVariantToSimple | <convertVariantToSimple> |
    And the following CSV file to import:
      """
      sku;family;parent;color;enabled
      1111111121;clothing;;red;0
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the "1111111121" product should <toBeOrNotToBe> variant
    And product "1111111121" should be disabled
    And the product value color of "1111111121" should be "<color>"
    Examples:
      | enabledComparison | convertVariantToSimple | toBeOrNotToBe | color |
      | yes               | yes                    | not be        | red   |
      | no                | yes                    | not be        | red   |
      | yes               | no                     | be            | blue  |
      | no                | no                     | be            | blue  |
