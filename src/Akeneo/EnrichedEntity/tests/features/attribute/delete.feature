Feature: Delete an attribute linked to an enriched entity
  In order to modify an enriched entity
  As a user
  I want delete an attribute linked to an enriched entity

  Background:
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the following text attributes:
      | entity_identifier | code | labels                                    | required | order | value_per_channel | value_per_locale | max_length |
      | designer          | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true     | 0     | true              | false            | 44         |

  @acceptance-back
  Scenario: Delete a text attribute linked to an enriched entity
    When the user deletes the attribute "name" linked to the enriched entity "designer"
    Then there is no attribute "name" for the enriched entity "designer"

