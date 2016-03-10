@javascript
Feature: Import XLSX products
  In order to use existing product information
  As a product manager
  I need to be able to import products with XLSX files

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label     | type    |
      | CROSS | Bag Cross | RELATED |
    And I am logged in as "Julia"

  Scenario: Successfully import a xlsx file of products
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots;CROSS;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;sneakers;;sandals;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;boots;CROSS;winter_boots,sandals;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;boots;CROSS;winter_boots;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;sneakers;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;boots;CROSS;winter_boots;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;sneakers;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;boots;CROSS;sandals;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 10 products
    And the family of the product "SKU-006" should be "boots"
    And product "SKU-007" should be enabled
    And the english tablet name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-002" should be "Pellentesque habitant morbi tristique senectus et netus et malesuada fames"

  Scenario: Successfully import a XLSX file of product with carriage return in product description
    Given I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "product_with_carriage_return.xlsx"
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 1 products
    And the english tablet description of "SKU-001" should be "dictum magna.|NL||NL|Lorem ispum|NL|Est"
