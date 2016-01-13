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
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 3 proposals
    And I should get the following proposals:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"10.25"}]}]}} |
      | clothing_product_proposal_import | SKU-002 | {"values":{"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"10"}]}]}}    |
      | clothing_product_proposal_import | SKU-003 | {"values":{"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"10.00"}]}]}} |

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
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 3 proposals
    And I should get the following proposals:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"0.2500"}}]}} |
      | clothing_product_proposal_import | SKU-002 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"2.0000"}}]}} |
      | clothing_product_proposal_import | SKU-003 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"5.0000"}}]}} |

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
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 3 proposals
    And I should get the following proposals:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"0.2500"}}]}} |
      | clothing_product_proposal_import | SKU-002 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"2.0000"}}]}} |
      | clothing_product_proposal_import | SKU-003 | {"values":{"decimal_length":[{"locale":null,"scope":null,"data":{"unit":"METER","data":"5.0000"}}]}} |

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
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 7 proposals
    And I should get the following proposals:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 001"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.25"},{"currency":"USD","data":"199.00"}]}]}} |
      | clothing_product_proposal_import | SKU-002 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 002"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.00"},{"currency":"USD","data":"199.25"}]}]}} |
      | clothing_product_proposal_import | SKU-003 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 003"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.00"},{"currency":"USD","data":"199.00"}]}]}} |
      | clothing_product_proposal_import | SKU-004 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 004"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.00"},{"currency":"USD","data":"199.00"}]}]}} |
      | clothing_product_proposal_import | SKU-005 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 005"}]}}                                                                                                                       |
      | clothing_product_proposal_import | SKU-006 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 006"}]}}                                                                                                                       |
      | clothing_product_proposal_import | SKU-007 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 007"}]}}                                                                                                                       |

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
    When I am on the "clothing_product_proposal_import" import job page
    And I launch the import job
    And I wait for the "clothing_product_proposal_import" job to finish
    Then there should be 4 proposals
    And I should get the following proposals:
      | username                         | product | result                                                                                                                                                                                                                                                                    |
      | clothing_product_proposal_import | SKU-001 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 001"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.25"},{"currency":"USD","data":"199.00"}]}]}} |
      | clothing_product_proposal_import | SKU-002 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 002"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.00"},{"currency":"USD","data":"199.25"}]}]}} |
      | clothing_product_proposal_import | SKU-003 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 003"}],"price":[{"locale":null,"scope":null,"data":[{"currency":"EUR","data":"125.00"},{"currency":"USD","data":"199.00"}]}]}} |
      | clothing_product_proposal_import | SKU-004 | {"values":{"name":[{"locale":"en_US","scope":null,"data":"sku 004"}]}}                                                                                                                       |

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
