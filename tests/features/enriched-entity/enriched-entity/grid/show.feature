Feature: Show enriched entity
  In order to see the details of an enriched entity
  As a user
  I want see the details of an enriched entity

#  @acceptance-front @acceptance-back
  Scenario: Getting a single entity
    Given the following enriched entities to show:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user asks for the enriched entity "designer"
    And the user gets the enriched entity "designer" with label "Designer"

#  @acceptance-back
  Scenario: Do not show the enriched entity if it does not exist
    When the user asks for the enriched entity "manufacturer"
    Then there is no enriched entity found for this identifier
