Feature: Filter on string
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by string

  Scenario: Successfully filter on string
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | name-en_US    |
      | BOOTBXS | Boot black xs |
      | BOOTWXS | Boot white xs |
      | BOOTBS  | Boot black s  |
      | BOOTBL  | Mug           |
      | BOOTRXS |               |
    Then I should get the following results for the given filters:
      | filter                                                                                                     | result                                     |
      | [{"field":"name", "operator":"STARTS WITH",      "value": "Boot",         "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"]           |
      | [{"field":"name", "operator":"EMPTY",            "value": null,           "context": {"locale": "en_US"}}] | ["BOOTRXS"]                                |
      | [{"field":"name", "operator":"NOT EMPTY",        "value": null,           "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS", "BOOTBS", "BOOTBL"] |
      | [{"field":"name", "operator":"STARTS WITH",      "value": "boot",         "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"]           |
      | [{"field":"name", "operator":"ENDS WITH",        "value": "xs",           "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS"]                     |
      | [{"field":"name", "operator":"CONTAINS",         "value": "black",        "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTBS"]                      |
      | [{"field":"name", "operator":"DOES NOT CONTAIN", "value": "Boot",         "context": {"locale": "en_US"}}] | ["BOOTBL"]                                 |
      | [{"field":"name", "operator":"=",                "value": "Boot black s", "context": {"locale": "en_US"}}] | ["BOOTBS"]                                 |
      | [{"field":"name", "operator":"=",                "value": "Mug ",         "context": {"locale": "en_US"}}] | []                                         |
      | [{"field":"name", "operator":"!=",               "value": "Mug",          "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTBS", "BOOTWXS"]           |
