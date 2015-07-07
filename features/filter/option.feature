Feature: Filter on select attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by select attributes

  Scenario: Successfully filter on select attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | size |
      | BOOTBXS | 40   |
      | BOOTWXS | 38   |
      | BOOTBS  | 44   |
      | BOOTRXS |      |
    Then I should get the following results for the given filters:
      | filter                                                              | result                |
      | [{"field":"size.code", "operator":"IN",    "value": ["44"] }]       | ["BOOTBS"]            |
      | [{"field":"size.code", "operator":"IN",    "value": ["44", "38"] }] | ["BOOTBS", "BOOTWXS"] |
      | [{"field":"size.code", "operator":"EMPTY", "value": null }]         | ["BOOTRXS"]           |
