@javascript
Feature: Import product information with decimal
  In order to use existing product information
  As a product manager
  I need to be able to import localized products

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a number attribute
    Given the following CSV file to import:
      """
      sku;rate_sale
      SKU-001;10,25
      SKU-002;10
      SKU-003;10,00
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Then the product "SKU-001" should have the following values:
      | rate_sale | 10.25 |
    Then the product "SKU-002" should have the following values:
      | rate_sale | 10.00 |
    Then the product "SKU-003" should have the following values:
      | rate_sale | 10.00 |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a metric attribute splitting the data and unit
    Given the following CSV file to import:
      """
      sku;weight;weight-unit
      SKU-001;425,25;GRAM
      SKU-002;425;GRAM
      SKU-003;425,00;GRAM
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Then the product "SKU-001" should have the following values:
      | weight | 425.2500 GRAM |
    Then the product "SKU-002" should have the following values:
      | weight | 425.0000 GRAM |
    Then the product "SKU-003" should have the following values:
      | weight | 425.0000 GRAM |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a metric attribute
    Given the following CSV file to import:
      """
      sku;weight
      SKU-001;425,25 GRAM
      SKU-002;425 GRAM
      SKU-003;425,00 GRAM
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 3 products
    Then the product "SKU-001" should have the following values:
      | weight | 425.2500 GRAM |
    Then the product "SKU-002" should have the following values:
      | weight | 425.0000 GRAM |
    Then the product "SKU-003" should have the following values:
      | weight | 425.0000 GRAM |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a price attribute splitting the data and currency
    Given the following CSV file to import:
      """
      sku;price;name-en_US
      SKU-001;"125,25 EUR, 199 USD";"sku 001"
      SKU-002;"125 EUR, 199,25 USD";"sku 002"
      SKU-003;"125,00 EUR, 199,00 USD";"sku 003"
      SKU-004;"125,00 EUR,199,00 USD";"sku 004"
      SKU-005;"";"sku 005"
      SKU-006;" EUR, USD";"sku 006"
      SKU-007;"EUR,USD";"sku 007"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 7 products
    Then the product "SKU-001" should have the following values:
      | price | 125.25 EUR, 199.00 USD |
    Then the product "SKU-002" should have the following values:
      | price | 125.00 EUR, 199.25 USD |
    Then the product "SKU-003" should have the following values:
      | price | 125.00 EUR, 199.00 USD |
    Then the product "SKU-004" should have the following values:
      | price | 125.00 EUR, 199.00 USD |
    Then the product "SKU-005" should have the following values:
      | price |  |
    Then the product "SKU-006" should have the following values:
      | price |  |
    Then the product "SKU-007" should have the following values:
      | price |  |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a price attribute splitting the data and currency
    Given the following CSV file to import:
      """
      sku;price-EUR;price-USD;name-en_US
      SKU-001;"125,25";"199";"sku 001"
      SKU-002;"125";"199,25";"sku 002"
      SKU-003;"125,00";"199,00";"sku 003"
      SKU-004;"";"";"sku 004"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 4 products
    Then the product "SKU-001" should have the following values:
      | price | 125.25 EUR, 199.00 USD |
    Then the product "SKU-002" should have the following values:
      | price | 125.00 EUR, 199.25 USD |
    Then the product "SKU-003" should have the following values:
      | price | 125.00 EUR, 199.00 USD |
    Then the product "SKU-004" should have the following values:
      | price |  |

  Scenario: Skip product with a decimal separator different as configuration
    Given the following CSV file to import:
      """
      sku;price
      SKU-001;"125,25 EUR, 199 USD"
      SKU-002;"125 EUR, 199,25 USD"
      SKU-003;"125,00 EUR, 199,00 USD"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | .                |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "skipped 3"
    And I should see the text "This type of value expects the use of a dot (.) to separate decimals."
