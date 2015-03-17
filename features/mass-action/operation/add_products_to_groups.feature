Feature: Run mass edit actions to add groups to many products at once
  In order to add groups to products
  I need to be able to run mass edit products operation

  Scenario: Successfully mass-edit products to add groups to them
    Given the "apparel" catalog configuration
    And the following products:
      | sku       |
      | Tormentor |
      | Griffin   |
      | Catalyst  |
      | Exequror  |
      | Caracal   |
    Given the following product groups:
      | code        | label         | type    |
      | cruiser     | Cruisers      | RELATED |
      | dreadnought | Dreadnoughts  | RELATED |
      | freight     | Freights      | RELATED |
      | miningbarge | Mining barges | RELATED |
      | destroyer   | Destroyers    | RELATED |
      | frigate     | Frigates      | RELATED |
    Then I should get the following products after apply the following mass-edit operation to it:
      | operation     | filters                                                               | actions                                                        | result                                     |
      | add-to-groups | [{"field":"sku", "operator":"IN", "value": ["Tormentor", "Griffin"]}] | [{"field": "groups", "value": ["cruiser"]}]                    | {"groups": ["cruiser"]}                    |
      | add-to-groups | [{"field":"sku", "operator":"=", "value": "Catalyst"}]                | [{"field": "groups", "value": ["destroyer", "cruiser"]}]       | {"groups": ["cruiser", "destroyer"]}       |
      | add-to-groups | [{"field":"sku", "operator":"IN", "value": ["Caracal", "Exequror"]}]  | [{"field": "groups", "value": ["miningbarge", "dreadnought"]}] | {"groups": ["dreadnought", "miningbarge"]} |
    When I apply the following mass-edit operation with the given configuration:
      | operation     | filters                                                 | actions                                     |
      | add-to-groups | [{"field":"enabled", "operator":"=", "value": true}]    | [{"field": "groups", "value": ["frigate"]}] |
      | add-to-groups | [{"field":"sku", "operator":"=", "value": "Tormentor"}] | [{"field": "groups", "value": ["freight"]}] |
    Then "cruiser" group should contain "Tormentor, Griffin, Catalyst"
    And "destroyer" group should contain "Catalyst"
    And "miningbarge" group should contain "Caracal, Exequror"
    And "dreadnought" group should contain "Caracal, Exequror"
    And "frigate" group should contain "Tormentor, Griffin, Catalyst, Exequror, Caracal"
    And "freight" group should contain "Tormentor"
