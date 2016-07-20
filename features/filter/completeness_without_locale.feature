Feature: Filter on completeness
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by completeness without specifying any locale

  Scenario: Successfully filter on completeness without specifying any locale
    Given an "apparel" catalog configuration
    And the following products:
      | sku     | family  | name-en_US      | name-en_GB      | price         | size   | color | manufacturer     |
      | TSHIRTS | tshirts | T-shirt S Black | T-shirt S Black | 10 USD, 9 GBP | size_S | black | american_apparel |
      | TSHIRTM | tshirts | T-shirt M Black |                 | 10 USD, 9 GBP | size_M | black | american_apparel |
      | TSHIRTL | tshirts | T-shirt L Black |                 | 10 USD, 9 GBP | size_L |       |                  |
    Then I should get the following results for the given filters:
      | filter                                                                       | result                 |
      | [{"field":"completeness", "operator":"=",  "value": 100, "context": {"scope": "tablet"}}] | ["TSHIRTS", "TSHIRTM"] |
      | [{"field":"completeness", "operator":"<",  "value": 100, "context": {"scope": "tablet"}}] | ["TSHIRTM", "TSHIRTL"] |
      | [{"field":"completeness", "operator":"!=", "value": 100, "context": {"scope": "tablet"}}] | ["TSHIRTM", "TSHIRTL"] |
