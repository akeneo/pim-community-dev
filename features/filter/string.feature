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
      | filter                                                                                        | result                           |
      | [{"field":"name", "operator":"STARTS WITH",      "value": "Boot",         "locale": "en_US"}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"name", "operator":"STARTS WITH",      "value": "boot",         "locale": "en_US"}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"name", "operator":"ENDS WITH",        "value": "xs",           "locale": "en_US"}] | ["BOOTBXS", "BOOTWXS"]           |
      | [{"field":"name", "operator":"CONTAINS",         "value": "black",        "locale": "en_US"}] | ["BOOTBXS", "BOOTBS"]            |
      | [{"field":"name", "operator":"DOES NOT CONTAIN", "value": "Boot",         "locale": "en_US"}] | ["BOOTBL"]                       |
      | [{"field":"name", "operator":"=",                "value": "Boot black s", "locale": "en_US"}] | ["BOOTBS"]                       |
      | [{"field":"name", "operator":"=",                "value": "Mug ",         "locale": "en_US"}] | []                               |
      | [{"field":"name", "operator":"EMPTY",            "value": null,           "locale": "en_US"}] | ["BOOTRXS"]                      |

  Scenario: Filter string with special character
    Given a "footwear" catalog configuration
    And the following products:
      | sku       | name-en_US |
      | BOOTBOOT1 | _          |
      | BOOTBOOT2 | %          |
    Then I should get the following results for the given filters:
      | filter                                                                                        | result        |
      | [{"field":"name", "operator":"DOES NOT CONTAIN", "value": "_", "locale": "en_US"}] | ["BOOTBOOT2"] |
      | [{"field":"name", "operator":"DOES NOT CONTAIN", "value": "%", "locale": "en_US"}] | ["BOOTBOOT1"] |
      | [{"field":"name", "operator":"CONTAINS", "value": "_",         "locale": "en_US"}] | ["BOOTBOOT1"] |
      | [{"field":"name", "operator":"CONTAINS", "value": "%",         "locale": "en_US"}] | ["BOOTBOOT2"] |