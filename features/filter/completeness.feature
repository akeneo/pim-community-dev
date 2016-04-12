Feature: Filter on completeness
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by completeness

  Scenario: Successfully filter on completeness
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | family | name-en_US    | price             | size | color |
      | BOOTBXS | boots  | Boot 42 Black | 10.00 USD, 10 EUR | 42   | black |
      | BOOTWXS | boots  | Boot 42 Black | 10.00 USD, 10 EUR | 42   |       |
      | BOOTBS  | boots  |               |                   | 38   | black |
      | BOOTBL  | boots  |               |                   |      |       |
      | BOOTRXS | boots  | Boot 42 Red   |                   |      |       |
    And I launched the completeness calculator
    Then I should get the following results for the given filters:
      | filter                                                                                          | result                                     |
      | [{"field":"completeness", "operator":"=",  "value": 100, "locale": "en_US", "scope": "mobile"}] | ["BOOTBXS"]                                |
      | [{"field":"completeness", "operator":"<",  "value": 25, "locale": "en_US", "scope": "mobile"}]  | ["BOOTBL"]                                 |
      | [{"field":"completeness", "operator":"<=", "value": 80, "locale": "en_US", "scope": "mobile"}]  | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTRXS"] |
      | [{"field":"completeness", "operator":">=", "value": 50, "locale": "en_US", "scope": "mobile"}]  | ["BOOTBS", "BOOTWXS", "BOOTBXS"]           |
      | [{"field":"completeness", "operator":">",  "value": 80, "locale": "en_US", "scope": "mobile"}]  | ["BOOTBXS"]                                |
      | [{"field":"completeness", "operator":"!=", "value": 100, "locale": "en_US", "scope": "mobile"}] | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTRXS"] |

  Scenario: Successfully filter on completeness without specifying any locale
    Given an "apparel" catalog configuration
    And the following products:
      | sku     | family  | name-en_US      | name-en_GB      | price         | size   | color | manufacturer     |
      | TSHIRTS | tshirts | T-shirt S Black | T-shirt S Black | 10 USD, 9 GBP | size_S | black | american_apparel |
      | TSHIRTM | tshirts | T-shirt M Black |                 | 10 USD, 9 GBP | size_M | black | american_apparel |
      | TSHIRTL | tshirts | T-shirt L Black |                 | 10 USD, 9 GBP | size_L |       |                  |
    And I launched the completeness calculator
    Then I should get the following results for the given filters:
      | filter                                                                       | result                 |
      | [{"field":"completeness", "operator":"=",  "value": 100, "scope": "tablet"}] | ["TSHIRTS", "TSHIRTM"] |
      | [{"field":"completeness", "operator":"<",  "value": 100, "scope": "tablet"}] | ["TSHIRTM", "TSHIRTL"] |
