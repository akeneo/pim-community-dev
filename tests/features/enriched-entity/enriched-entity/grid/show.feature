Feature: Show enriched entity
  In order to see the details of an enriched entity
  As a user
  I want see the details of an enriched entity

  @acceptance-front
  Scenario: Getting a single entity
    Given the following enriched entities:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user ask for the enriched entity "designer"
    And I get the enriched entity "designer" with label "Designer"

  @acceptance-front @acceptance-back
  Scenario: Do not show the enriched entity if it does not exist
    When the user ask for the enriched entity list
    Then there is no enriched entity
