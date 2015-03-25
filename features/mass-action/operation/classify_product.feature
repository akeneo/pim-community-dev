Feature: Run mass edit actions to classify many products at once
  In order to add categories to products
  I need to be able to classify many products

  Scenario: Successfully mass-edit products to classify them
    Given the "apparel" catalog configuration
    And the following products:
      | sku     |
      | BOOTBXS |
      | BOOTWXS |
      | BOOTBS  |
      | BOOTBL  |
      | MUGRXS  |
    Then I should get the following products after apply the following mass-edit operation to it:
      | operation | filters                                                           | actions                                                             | result                                                               |
      | classify  | [{"field":"sku", "operator":"IN", "value": ["BOOTBL", "MUGRXS"]}] | [{"field": "categories", "value": ["2015_collection", "men_2013"]}] | {"categories": ["2015_collection", "men_2013"]}                      |
      | classify  | [{"field":"sku", "operator":"=", "value": "BOOTBS"}]              | [{"field": "categories", "value": ["women_2015_winter"]}]           | {"categories": ["women_2015_winter"]}                                |
      | classify  | [{"field":"sku", "operator":"IN", "value": ["BOOTBL", "MUGRXS"]}] | [{"field": "categories", "value": ["women_2015_autumn"]}]           | {"categories": ["2015_collection", "men_2013", "women_2015_autumn"]} |
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                              | actions                                                   |
      | classify  | [{"field":"enabled", "operator":"=", "value": true}] | [{"field": "categories", "value": ["2013_collection"]}]   |
      | classify  | [{"field":"sku", "operator":"=", "value": "BOOTBL"}] | [{"field": "categories", "value": ["women_2015_winter"]}] |
    Then the categories of "BOOTBS" should be "2013_collection, women_2015_winter"
    Then the categories of "MUGRXS" should be "2013_collection, 2015_collection, men_2013, women_2015_autumn"
    Then the categories of "BOOTBL" should be "2013_collection, 2015_collection, men_2013, women_2015_autumn, women_2015_winter"
