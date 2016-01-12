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

  @jira https://akeneo.atlassian.net/browse/PIM-5224
  Scenario: Successfully filter on multi select attributes that have the same option codes
    Given a "footwear" catalog configuration
    And the following attributes:
      | code            | label           | type        |
      | main_color      | Main color      | multiselect |
      | secondary_color | Secondary color | multiselect |
    And the following "main_color" attribute options: purple and red
    And the following "secondary_color" attribute options: purple and red
    And the following products:
      | sku        | main_color  | secondary_color |
      | high-heels | purple, red |                 |
      | rangers    |             | purple,red      |
    Then I should get the following results for the given filters:
      | filter                                                                           | result         |
      | [{"field":"main_color.code", "operator":"IN", "value": ["purple", "red"] }]      | ["high-heels"] |
      | [{"field":"secondary_color.code", "operator":"IN", "value": ["purple", "red"] }] | ["rangers"]    |
