@javascript
Feature: Import proposals with decimals
  In order to generate proposals
  As a redactor
  I need to be able to import proposals with decimals

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku     | categories        |
      | SKU-001 | summer_collection |
      | SKU-002 | summer_collection |
      | SKU-003 | summer_collection |
      | SKU-004 | summer_collection |
      | SKU-005 | summer_collection |
      | SKU-006 | summer_collection |
      | SKU-007 | summer_collection |
    And the following attributes:
      | code           | label          | type   | decimals_allowed | metric_family | default metric unit | useable_as_grid_filter |
      | decimal_length | Decimal_length | metric | yes              | Length        | CENTIMETER          | yes                    |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a number attribute
    Given the following CSV file to import:
      """
      sku;price-EUR
      SKU-001;10,25
      SKU-002;10
      SKU-003;10,00
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    And I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    When I am on the proposals page
    Then the grid should contain 3 element
    And I should see the following proposals:
      | product | author                           | attribute | original | new    |
      | SKU-001 | clothing_product_proposal_import | price     |          | €10.25 |
      | SKU-002 | clothing_product_proposal_import | price     |          | €10.00 |
      | SKU-003 | clothing_product_proposal_import | price     |          | €10.00 |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a metric attribute splitting the data and unit
    Given the following CSV file to import:
      """
      sku;decimal_length;decimal_length-unit
      SKU-001;0,25;METER
      SKU-002;2;METER
      SKU-003;5,00;METER
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    And I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    When I am on the proposals page
    Then the grid should contain 3 element
    And I should see the following proposals:
      | product | author                           | attribute      | original | new        |
      | SKU-001 | clothing_product_proposal_import | decimal_length |          | 0.25 Meter |
      | SKU-002 | clothing_product_proposal_import | decimal_length |          | 2 Meter    |
      | SKU-003 | clothing_product_proposal_import | decimal_length |          | 5 Meter    |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a metric attribute
    Given the following CSV file to import:
      """
      sku;decimal_length
      SKU-001;0,25 METER
      SKU-002;2 METER
      SKU-003;5,00 METER
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    And I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    When I am on the proposals page
    Then the grid should contain 3 element
    And I should see the following proposals:
      | product | author                           | attribute      | original | new        |
      | SKU-001 | clothing_product_proposal_import | decimal_length |          | 0.25 Meter |
      | SKU-002 | clothing_product_proposal_import | decimal_length |          | 2 Meter    |
      | SKU-003 | clothing_product_proposal_import | decimal_length |          | 5 Meter    |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a price attribute splitting the data and currency
    Given the following CSV file to import:
      """
      sku;price;name-en_US
      SKU-001;"125,25 EUR, 199 USD";"sku 001"
      SKU-002;"125 EUR, 199,25 USD";"sku 002"
      SKU-003;"125,00 EUR. 199,00 USD";"sku 003"
      SKU-004;"125,00 EUR.199,00 USD";"sku 004"
      SKU-005;"";"sku 005"
      SKU-006;" EUR, USD";"sku 006"
      SKU-007;"EUR,USD";"sku 007"
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    And I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    When I am on the proposals page
    Then the grid should contain 7 element
    And I should see the following proposals:
      | product | author                           | attribute | locale | original | new             |
      | SKU-001 | clothing_product_proposal_import | name      | en_US  |          | sku 001         |
      | SKU-001 | clothing_product_proposal_import | price     |        |          | €125.25,$199.00 |
      | SKU-002 | clothing_product_proposal_import | name      | en_US  |          | sku 002         |
      | SKU-002 | clothing_product_proposal_import | price     |        |          | €125.00,$199.25 |
      | SKU-003 | clothing_product_proposal_import | name      | en_US  |          | sku 003         |
      | SKU-003 | clothing_product_proposal_import | price     |        |          | €125.00,$199.00 |
      | SKU-004 | clothing_product_proposal_import | name      | en_US  |          | sku 004         |
      | SKU-004 | clothing_product_proposal_import | price     |        |          | €125.00,$199.00 |
      | SKU-005 | clothing_product_proposal_import | name      | en_US  |          | sku 005         |
      | SKU-006 | clothing_product_proposal_import | name      | en_US  |          | sku 006         |
      | SKU-007 | clothing_product_proposal_import | name      | en_US  |          | sku 007         |

  Scenario: Successfully import a csv file (with decimal separator as a comma) with a price attribute splitting the data and currency
    Given the following CSV file to import:
      """
      sku;price-EUR;price-USD;name-en_US
      SKU-001;"125,25";"199";"sku 001"
      SKU-002;"125";"199,25";"sku 002"
      SKU-003;"125,00";"199,00";"sku 003"
      SKU-004;"";"";"sku 004"
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | ,                |
    And I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    When I am on the proposals page
    Then the grid should contain 4 element
    And I should see the following proposals:
      | product | author                           | attribute | locale | original | new             |
      | SKU-001 | clothing_product_proposal_import | name      | en_US  |          | sku 001         |
      | SKU-001 | clothing_product_proposal_import | price     |        |          | €125.25,$199.00 |
      | SKU-002 | clothing_product_proposal_import | name      | en_US  |          | sku 002         |
      | SKU-002 | clothing_product_proposal_import | price     |        |          | €125.00,$199.25 |
      | SKU-003 | clothing_product_proposal_import | name      | en_US  |          | sku 003         |
      | SKU-003 | clothing_product_proposal_import | price     |        |          | €125.00,$199.00 |
      | SKU-004 | clothing_product_proposal_import | name      | en_US  |          | sku 004         |

  Scenario: Skip product with a decimal separator different from configuration
    Given the following CSV file to import:
      """
      sku;price
      SKU-001;"125,25 EUR, 199 USD"
      SKU-002;"125 EUR, 199,25 USD"
      SKU-003;"125,00 EUR, 199,00 USD"
      """
    And the following job "clothing_product_proposal_import" configuration:
      | filePath         | %file to import% |
      | decimalSeparator | .                |
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then I should see "skipped 3"
    And I should see "This type of value expects the use of . to separate decimals."
