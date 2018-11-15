Feature: Show reference entity
  In order to see the details of a reference entity
  As a user
  I want see the details of a reference entity

  @acceptance-front
  Scenario: Getting a single entity
    Given the following reference entities to show:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user asks for the reference entity "designer"
    And the user gets the reference entity "designer" with label "Designer"
