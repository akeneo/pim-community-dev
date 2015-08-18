@deprecated @javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code  | label     | type    |
      | CROSS | Bag Cross | RELATED |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new products with invalid prices during an import
    Given the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      SKU-002;50 EUR
      SKU-003;invalid
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 2 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "SKU-002" should have the following value:
      | price | 50.00 EUR |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with invalid prices during an import
    Given the following products:
      | sku     | price  |
      | SKU-001 | 99 EUR |
      | SKU-002 | 45 EUR |
      | SKU-003 | 12 EUR |
    And the following CSV file to import:
      """
      sku;price
      SKU-001;"100 EUR, 90 USD"
      SKU-002;50 EUR
      SKU-003;invalid
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 3 products
    And the product "SKU-001" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "SKU-002" should have the following value:
      | price | 50.00 EUR |
    And the product "SKU-003" should have the following value:
      | price | 12.00 EUR |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new products with invalid metrics during an import
    Given the following CSV file to import:
      """
      sku;length
      SKU-001;4000 CENTIMETER
      SKU-002;invalid
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 1 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |

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
      SKU-002;invalid
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 2 products
    And the product "SKU-001" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "SKU-002" should have the following value:
      | length | 2.0000 KILOMETER |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new products with invalid regular attributes during an import
    Given the following products:
      | sku     | number_in_stock |
      | SKU-001 | 4000            |
    And the following CSV file to import:
      """
      sku;number_in_stock
      SKU-001;2000
      SKU-002;invalid_stock
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 1 product
    And the product "SKU-001" should have the following value:
      | number_in_stock | 2000.0000 |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with invalid regular attributes during an import
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
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 2 products
    And the product "SKU-001" should have the following value:
      | number_in_stock | 4000 |
    And the product "SKU-002" should have the following value:
      | number_in_stock | 100 |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new products with non-existing media attributes during an import
    Given the following attributes:
      | label       | type  | allowed extensions |
      | Front view  | image | gif, jpg           |
      | User manual | file  | txt, pdf           |
    And the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;sneakers;;invalid-front-view.gif;"Bic Core 148";invalid-user-manual.txt;2014_collection
      fanatic-freewave-76;sneakers;;fanatic-freewave-76.gif;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    And there should be 1 product
    And I should see "frontView: File not found"
    And I should see "userManual: File not found"
    And the product "fanatic-freewave-76" should have the following values:
      | name-en_US | Fanatic Freewave 76     |
      | frontView  | fanatic-freewave-76.gif |
      | userManual | fanatic-freewave-76.txt |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing products with non-existing media attributes during an import
    Given the following products:
      | sku                 | family   | name-en_US          | categories      |
      | bic-core-148        | sneakers | Bic Core 148        | 2014_collection |
      | fanatic-freewave-76 | sneakers | Fanatic Freewave 76 | 2014_collection |
    And the following attributes:
      | label       | type  | allowed extensions |
      | Front view  | image | gif, jpg           |
      | User manual | file  | txt, pdf           |
    And the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;sneakers;;invalid-front-view.gif;"New Bic Core 148";invalid-user-manual.txt;2014_collection
      fanatic-freewave-76;sneakers;;fanatic-freewave-76.gif;"New Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 1"
    And there should be 2 products
    And I should see "frontView: File not found"
    And I should see "userManual: File not found"
    And the product "fanatic-freewave-76" should have the following values:
      | frontView  | fanatic-freewave-76.gif |
      | userManual | fanatic-freewave-76.txt |
      | name-en_US | New Fanatic Freewave 76 |
    And the product "bic-core-148" should have the following values:
      | frontView  | **empty**    |
      | userManual | **empty**    |
      | name-en_US | Bic Core 148 |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip products with empty SKU
    Given the following CSV file to import:
      """
      sku;name-en_US
      ;invalid product
      ;another invalid product
      SKU-002;product 002
      ;last invalid product
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "skipped 3"
    And I should see "identifier: This value should not be blank"
    And there should be 1 product
    And the product "SKU-002" should have the following value:
      | name-en_US | product 002 |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip products with a SKU that has just been created
    Given the following CSV file to import:
      """
      sku;name-en_US
      SKU-001;high heels
      SKU-001;invalid high heels
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "The unique code"
    And I should see "was already read in this file"
    And there should be 1 product
    And the product "SKU-001" should have the following value:
      | name-en_US | high heels |

  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where local is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;name-en_US;description-wronglocale-ecommerce
      SKU-001;high heels;red high heels
      SKU-002;rangers;black rangers
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "PRODUCT IMPORT Locale wronglocale does not exist."
    And I should see "FAILED"
    And there should be 0 product

  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where channel is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;name-en_US;description-en_US-wrongchannel
      SKU-001;high heels;red high heels
      SKU-002;rangers;black rangers
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "PRODUCT IMPORT Channel wrongchannel does not exist."
    And I should see "FAILED"
    And there should be 0 product

  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where channel is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;price-FCFA
      SKU-001;100
      SKU-002;50
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "PRODUCT IMPORT Currency FCFA does not exist."
    And I should see "FAILED"
    And there should be 0 product
