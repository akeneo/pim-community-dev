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
      | filter                                                                 | result                           |
      | [{"field":"size.code", "operator":"NOT EMPTY", "value": null }]        | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"size.code", "operator":"IN",        "value": ["44"]}]       | ["BOOTBS"]                       |
      | [{"field":"size.code", "operator":"IN",        "value": ["44", "38"]}] | ["BOOTBS", "BOOTWXS"]            |
      | [{"field":"size.code", "operator":"NOT IN",    "value": ["44", "38"]}] | ["BOOTBXS"]                      |
      | [{"field":"size.code", "operator":"EMPTY",     "value": null}]         | ["BOOTRXS"]                      |

  @jira https://akeneo.atlassian.net/browse/PIM-5224
  Scenario: Successfully filter on select attributes that have the same option codes
    Given a "footwear" catalog configuration
    And the following attributes:
      | code            | label-en_US     | type                     | group |
      | main_color      | Main color      | pim_catalog_simpleselect | other |
      | secondary_color | Secondary color | pim_catalog_simpleselect | other |
    And the following "main_color" attribute options: purple
    And the following "secondary_color" attribute options: purple
    And the following products:
      | sku        | main_color | secondary_color |
      | high-heels | purple     |                 |
      | rangers    |            | purple          |
    Then I should get the following results for the given filters:
      | filter                                                                    | result         |
      | [{"field":"main_color.code", "operator":"IN", "value": ["purple"] }]      | ["high-heels"] |
      | [{"field":"secondary_color.code", "operator":"IN", "value": ["purple"] }] | ["rangers"]    |
