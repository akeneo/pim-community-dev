Feature: Run mass edit actions to add several products into a variant group
  In order to add products into a variant group
  I need to be able to run mass edit products operation

  Scenario: Successfully mass-edit products to add them into a variant group
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
      | cruiser     | Cruisers      | VARIANT |
      | dreadnought | Dreadnoughts  | VARIANT |
      | freight     | Freights      | VARIANT |
      | miningbarge | Mining barges | VARIANT |
      | destroyer   | Destroyers    | VARIANT |
      | frigate     | Frigates      | VARIANT |
    Then I should get the following products after apply the following mass-edit operation to it:
      | operation            | filters                                                               | actions                                              | result                           |
      | add-to-variant-group | [{"field":"sku", "operator":"IN", "value": ["Tormentor", "Griffin"]}] | [{"field": "variant_group", "value": "cruiser"}]     | {"groups": ["cruiser"]}     |
      | add-to-variant-group | [{"field":"sku", "operator":"=", "value": "Catalyst"}]                | [{"field": "variant_group", "value": "destroyer"}]   | {"groups": ["destroyer"]}   |
      | add-to-variant-group | [{"field":"sku", "operator":"IN", "value": ["Caracal", "Exequror"]}]  | [{"field": "variant_group", "value": "dreadnought"}] | {"groups": ["dreadnought"]} |
    Then "cruiser" group should contain "Tormentor, Griffin"
    And "destroyer" group should contain "Catalyst"
    And "dreadnought" group should contain "Caracal, Exequror"
