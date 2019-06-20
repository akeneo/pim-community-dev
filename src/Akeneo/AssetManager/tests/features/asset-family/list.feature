Feature: List reference entities
  In order to see what reference entities I have
  As a user
  I want see a list of reference entities

  @acceptance-front
  Scenario: List existing reference entities
    Given the following reference entities to list:
      | identifier |
      | designer   |
      | sofa       |
    When the user asks for the reference entity list
    Then the user gets a selection of 2 items out of 2 items in total
    And the user gets a reference entity "designer"
    And the user gets a reference entity "sofa"

  @acceptance-front
  Scenario: Shows an empty list if there is no reference entity
    When the user asks for the reference entity list
    Then there is no reference entity
