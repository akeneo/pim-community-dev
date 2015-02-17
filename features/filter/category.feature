Feature: Filter on category
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by category

  Scenario: Successfully filter on category
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | categories                         |
      | BOOTBXS | winter_boots, winter_collection    |
      | BOOTWXS | winter_boots, sandals              |
      | BOOTBS  | sandals                            |
      | BOOTBL  |                                    |
      | BOOTRXS | 2014_collection, summer_collection |
    Then I should get the following results for the given filters:
      | filter                                                                                               | result                           |
      | [{"field":"categories.code", "operator":"IN",                 "value": ["winter_boots", "sandals"]}] | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"categories.code", "operator":"NOT IN",             "value": ["winter_boots"]}]            | ["BOOTBS", "BOOTBL", "BOOTRXS"]  |
      | [{"field":"categories.code", "operator":"UNCLASSIFIED",       "value": []}]                          | ["BOOTBL"]                       |
      | [{"field":"categories.code", "operator":"IN OR UNCLASSIFIED", "value": ["sandals"]}]                 | ["BOOTBS", "BOOTBL", "BOOTWXS"]  |
      | [{"field":"categories.code", "operator":"IN CHILDREN",        "value": ["summer_collection"]}]       | ["BOOTBS", "BOOTRXS", "BOOTWXS"] |
      | [{"field":"categories.code", "operator":"NOT IN CHILDREN",    "value": ["winter_collection"]}]       | ["BOOTBS", "BOOTBL", "BOOTRXS"]  |
      | [{"field":"categories.code", "operator":"NOT IN CHILDREN",    "value": ["winter_collection"]}, {"field":"categories.code", "operator":"IN", "value": ["sandals"]}] | ["BOOTBS"] |
      | [{"field":"categories.code", "operator":"NOT IN CHILDREN",    "value": ["winter_collection"]}, {"field":"categories.code", "operator":"IN OR UNCLASSIFIED", "value": ["sandals"]}] | ["BOOTBS", "BOOTBL"] |
