Feature: List enriched entities
  In order to see what enriched entities I have
  As a user
  I want see a list of enriched entities

  @acceptance-front @acceptance-back
  Scenario: List existing enriched entities
    Given the following enriched entities:
      | identifier |
      | designer   |
      | sofa       |
    When the user ask for the enriched entity list
    Then the user get a selection of 2 items out of 2 items in total
    And I get an enriched entity "designer"
    And I get an enriched entity "sofa"

  @acceptance-back
  Scenario: Do not show the enriched entity if it does not exist
    When the user ask for the enriched entity list
    Then there is no enriched entity
