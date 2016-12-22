Feature: Filter on family
  In order to filter on families
  As an internal process or any user
  I need to be able to filter on product by family

  Scenario: Successfully filter on family
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | family   |
      | BOOTBXS | boots    |
      | HEELXXL | heels    |
      | SNEAKXS | sneakers |
      | BOOTBL  |          |
      | BOOTRXS |          |
    Then I should get the following results for the given filters:
      | filter                                                                            | result                            |
      | [{"field":"family", "operator":"IN",        "value": ["boots", "heels"]}]    | ["BOOTBXS", "HEELXXL"]            |
      | [{"field":"family", "operator":"NOT IN",    "value": ["heels", "sneakers"]}] | ["BOOTBXS", "BOOTBL", "BOOTRXS"]  |
      | [{"field":"family", "operator":"EMPTY",     "value": null}]                  | ["BOOTBL", "BOOTRXS"]             |
      | [{"field":"family", "operator":"NOT EMPTY", "value": null}]                  | ["BOOTBXS", "HEELXXL", "SNEAKXS"] |
