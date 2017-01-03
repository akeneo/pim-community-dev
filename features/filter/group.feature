Feature: Filter on groups
  In order to filter on groups
  As an internal process or any user
  I need to be able to filter on product by group

  Scenario: Successfully filter on groups
    Given a "apparel" catalog configuration
    And the following products:
      | sku    | groups             |
      | TSHIRT | upsell, related    |
      | JACKET | substitute         |
      | SWEAT  | upsell, substitute |
      | PANT   | related            |
      | BOOT   |                    |
    Then I should get the following results for the given filters:
      | filter                                                                               | result                                |
      | [{"field":"groups.code", "operator":"IN",        "value": ["substitute", "upsell"]}] | ["TSHIRT", "JACKET", "SWEAT"]         |
      | [{"field":"groups.code", "operator":"NOT IN",    "value": ["substitute", "upsell"]}] | ["PANT", "BOOT"]                      |
      | [{"field":"groups.code", "operator":"EMPTY",     "value": null}]                     | ["BOOT"]                              |
      | [{"field":"groups.code", "operator":"NOT EMPTY", "value": null}]                     | ["TSHIRT", "JACKET", "SWEAT", "PANT"] |
