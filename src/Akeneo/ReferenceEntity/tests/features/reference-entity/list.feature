Feature: List enriched entities
  In order to see what enriched entities I have
  As a user
  I want see a list of enriched entities

  @acceptance-front
  Scenario: List existing enriched entities
    Given the following enriched entities to list:
      | identifier |
      | designer   |
      | sofa       |
    When the user asks for the enriched entity list
    Then the user gets a selection of 2 items out of 2 items in total
    And the user gets an enriched entity "designer"
    And the user gets an enriched entity "sofa"

  @acceptance-front
  Scenario: Shows an empty list if there is no enriched entity
    When the user asks for the enriched entity list
    Then there is no enriched entity
