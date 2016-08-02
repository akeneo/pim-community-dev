Feature: Filter on media attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by media attributes

  Scenario: Successfully filter on media attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     |
      | BOOTBXS |
      | BOOTWXS |
      | BOOTBS  |
      | BOOTBL  |
      | BOOTBXL |
    And the following product values:
      | product | attribute | value                     |
      | BOOTBXS | side_view | %fixtures%/SNKRS-1R.png   |
      | BOOTWXS | side_view | %fixtures%/SNKRS-1C-s.png |
      | BOOTBS  | side_view | %fixtures%/SNKRS-1C-t.png |
      | BOOTBL  | side_view |                           |
    Then I should get the following results for the given filters:
      | filter                                                                                                          | result                           |
      | [{"field":"side_view", "operator":"STARTS WITH",      "value": "SNKRS",        "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"side_view", "operator":"STARTS WITH",      "value": "SNKRS-1C",     "context": {"locale": "en_US"}}] | ["BOOTWXS", "BOOTBS"]            |
      | [{"field":"side_view", "operator":"ENDS WITH",        "value": "s.png",        "context": {"locale": "en_US"}}] | ["BOOTWXS"]                      |
      | [{"field":"side_view", "operator":"CONTAINS",         "value": "-1C-",         "context": {"locale": "en_US"}}] | ["BOOTWXS", "BOOTBS"]            |
      | [{"field":"side_view", "operator":"DOES NOT CONTAIN", "value": "-1C-",         "context": {"locale": "en_US"}}] | ["BOOTBXS"]                      |
      | [{"field":"side_view", "operator":"=",                "value": "SNKRS-1R.png", "context": {"locale": "en_US"}}] | ["BOOTBXS"]                      |
      | [{"field":"side_view", "operator":"EMPTY",            "value": null,           "context": {"locale": "en_US"}}] | ["BOOTBL", "BOOTBXL"]            |
      | [{"field":"side_view", "operator":"NOT EMPTY",        "value": null,           "context": {"locale": "en_US"}}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"side_view", "operator":"!=",               "value": "SNKRS-1R.png", "context": {"locale": "en_US"}}] | ["BOOTWXS", "BOOTBS"]            |
