@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with invalid prices during an import
    Given the following products:
      | sku     | price  |
      | SKU-001 | 99 EUR |
      | SKU-002 | 45 EUR |
      | SKU-003 | 12 EUR |
      | SKU-004 | 98 EUR |
      | SKU-005 | 32 USD |
      | SKU-006 | 77 USD |
      | SKU-007 | 08 EUR |
      | SKU-008 | 33 EUR |
    And the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      SKU-002;50 EUR
      SKU-003;12 invalid
      SKU-004;"gruik EUR, 90 USD"
      SKU-005;"25 EUR, 90 gruik"
      SKU-006;"25.gruik EUR, 90 USD"
      SKU-007;"25 EUR, 90.gruik USD"
      SKU-008; EUR
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 5"
    And there should be 8 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "SKU-002" should have the following value:
      | price | 50.00 EUR |
    And the product "SKU-003" should have the following value:
      | price | 12.00 EUR |
    And the product "SKU-004" should have the following value:
      | price | 98.00 EUR |
    And the product "SKU-005" should have the following value:
      | price | 32.00 USD |
    And the product "SKU-006" should have the following value:
      | price | 77.00 USD |
    And the product "SKU-007" should have the following value:
      | price | 8.00 EUR |
    And the product "SKU-008" should have the following value:
      | price |  |
    And I should see the text "This value should be a valid number.: gruik EUR"

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with invalid metrics during an import
    Given the following products:
      | sku     | length        |
      | SKU-001 | 98 CENTIMETER |
      | SKU-002 | 2 KILOMETER   |
    And the following CSV file to import:
      """
      sku;length
      SKU-001;4000 CENTIMETER
      SKU-002;12 invalid
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 1"
    And there should be 2 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "SKU-002" should have the following value:
      | length | 2.0000 KILOMETER |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with invalid number during an import
    Given the following products:
      | sku     | number_in_stock |
      | SKU-001 | 4000            |
      | SKU-002 | 99              |
    And the following CSV file to import:
      """
      sku;number_in_stock
      SKU-001;invalid_stock
      SKU-002;100
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 1"
    And there should be 2 products
    And the product "SKU-001" should have the following value:
      | number_in_stock | 4000 |
    And the product "SKU-002" should have the following value:
      | number_in_stock | 100 |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new products with non-existing media attributes during an import
    Given the following attributes:
      | label-en_US | type              | allowed_extensions | group | code       |
      | Front view  | pim_catalog_image | gif, jpg           | other | frontView  |
      | User manual | pim_catalog_file  | txt, pdf           | other | userManual |
    And the following family:
      | code         | attributes                |
      | media_family | frontView,name,userManual |
    And the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;media_family;;invalid-front-view.gif;"Bic Core 148";invalid-user-manual.txt;2014_collection
      fanatic-freewave-76;media_family;;fanatic-freewave-76.gif;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And there should be 1 product
    And I should see the text "Property \"frontView\" expects a valid pathname as data"
    And the product "fanatic-freewave-76" should have the following values:
      | name-en_US | Fanatic Freewave 76     |
      | frontView  | fanatic-freewave-76.gif |
      | userManual | fanatic-freewave-76.txt |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip products with a SKU that has just been created
    Given the following CSV file to import:
      """
      sku;name-en_US
      SKU-001;high heels
      SKU-001;invalid high heels
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 1 product
    And I should see the text "The same identifier is already set on another product: SKU-001"
    And the product "SKU-001" should have the following value:
      | name-en_US | high heels |

  Scenario: Skip new products with invalid boolean during an import
    Given the following CSV file to import:
      """
      sku;handmade
      SKU-001;1
      SKU-002;"1"
      SKU-003;0
      SKU-004;"0"
      SKU-005;yes
      SKU-006;patapouet
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 2"
    And there should be 4 products
    And the product "SKU-001" should have the following value:
      | handmade | 1 |
    And the product "SKU-002" should have the following value:
      | handmade | 1 |
    And the product "SKU-003" should have the following value:
      | handmade |  |
    And the product "SKU-004" should have the following value:
      | handmade |  |

  Scenario: Skip new products with invalid metric during an import
    Given the following CSV file to import:
      """
      sku;length
      renault-kangoo;2500 CENTIMETER
      honda-civic;2 METER
      seat-ibiza;4 TON
      fiat-panda;
      fiat-uno;12
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 2"
    And there should be 3 products
    And the product "renault-kangoo" should have the following value:
      | length | 2500.0000 CENTIMETER |
    And the product "honda-civic" should have the following value:
      | length | 2.0000 METER |
    And the product "fiat-panda" should have the following value:
      | length |  |

  Scenario: Skip new products with invalid metric (two columns) during an import
    Given the following CSV file to import:
      """
      sku;length;length-unit
      renault-kangoo;2500;CENTIMETER
      fiat-panda;;CENTIMETER
      fiat-uno;2000;
      fiat-500;;
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 1"
    And there should be 3 product
    And the product "renault-kangoo" should have the following value:
      | length | 2500.0000 CENTIMETER |
    And the product "fiat-500" should have the following value:
      | length |  |
    And the product "fiat-panda" should have the following value:
      | length |  |
