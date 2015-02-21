Feature: Filter on multi select attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by multi select attributes

  Scenario: Successfully filter on multi select attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | weather_conditions |
      | BOOTBXS | dry, wet           |
      | BOOTWXS | dry                |
      | BOOTBS  | hot                |
      | BOOTBL  | hot, wet           |
      | BOOTRXS |                    |
    Then I should get the following results for the given filters:
      | filter                                                                                                                                             | result                          |
      | [{"field":"weather_conditions.code", "operator":"IN",    "value": ["dry"] }]                                                                       | ["BOOTBXS", "BOOTWXS"]          |
      | [{"field":"weather_conditions.code", "operator":"IN",    "value": ["wet", "hot"] }]                                                                | ["BOOTBS", "BOOTBL", "BOOTBXS"] |
      | [{"field":"weather_conditions.code", "operator":"IN", "value": ["wet"] }, {"field":"weather_conditions.code", "operator":"IN", "value": ["hot"] }] | ["BOOTBL"]                      |
      | [{"field":"weather_conditions.code", "operator":"EMPTY", "value": null }]                                                                          | ["BOOTRXS"]                     |
