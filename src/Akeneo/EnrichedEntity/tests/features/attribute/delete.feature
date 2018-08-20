Feature: Delete an attribute linked to an enriched entity
  In order to modify an enriched entity
  As a user
  I want delete an attribute linked to an enriched entity

  Background:
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-back
  Scenario: Delete a text attribute linked to an enriched entity
    When the user deletes the attribute "name" linked to the enriched entity "designer"
    Then there is no attribute "name" for the enriched entity "designer"

