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
      | filter                                                                                         | result                                     |
      | [{"field":"completeness", "operator":"=", "value": 100, "locale": "en_US", "scope": "mobile"}] | ["BOOTBXS"]                                |
      | [{"field":"completeness", "operator":"<", "value": 25, "locale": "en_US", "scope": "mobile"}]  | ["BOOTBL"]                                 |
      | [{"field":"completeness", "operator":"<=", "value": 80, "locale": "en_US", "scope": "mobile"}] | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTRXS"] |
      | [{"field":"completeness", "operator":">=", "value": 50, "locale": "en_US", "scope": "mobile"}] | ["BOOTBS", "BOOTWXS", "BOOTBXS"]           |
      | [{"field":"completeness", "operator":">", "value": 80, "locale": "en_US", "scope": "mobile"}]  | ["BOOTBXS"]                                |
