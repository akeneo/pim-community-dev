Feature: Show enriched entity
  In order to see the details of an enriched entity
  As a user
  I want see the details of an enriched entity

  @acceptance-front
  Scenario: Getting a single entity
    Given the following enriched entities to show:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user asks for the enriched entity "designer"
    And the user gets the enriched entity "designer" with label "Designer"
