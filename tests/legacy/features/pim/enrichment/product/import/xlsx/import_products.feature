Feature: Import XLSX products
  In order to use existing product information
  As a product manager
  I need to be able to import products with XLSX files

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |

  Scenario: Successfully import an XLSX file of products
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
    When the products are imported via the job xlsx_footwear_product_import
    Then there should be 10 products
    And the family of the product "SKU-006" should be "boots"
    And product "SKU-007" should be enabled
    And the english localizable value name of "SKU-001" should be "Donec"
    And the english tablet description of "SKU-002" should be "Pellentesque habitant morbi tristique senectus et netus et malesuada fames"

  Scenario: Successfully import a XLSX file of products with count columns less than count headers
    Given the following XLSX file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;boots
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      """
    When the products are imported via the job xlsx_footwear_product_import
    Then there should be 2 products

  @jira https://akeneo.atlassian.net/browse/PIM-6085
  @javascript
  Scenario: Successfully import product associations with modified column name
    Given I am logged in as "Julia"
    And the following XLSX file to import:
      """
      sku;family;groupes;catégories;name-en_US;description-en_US-tablet;price;size;color
      SKU-001;boots;similar_boots;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est;"100 EUR, 90 USD";40;
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames;"100 EUR, 90 USD";37;red
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | yes              |
      | categoriesColumn  | catégories       |
      | groupsColumn      | groupes          |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then I should not see the text "The fields \"catégories, famille, groupes\" do not exist"
    Then there should be 2 products
