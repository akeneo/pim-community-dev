Feature: List asset families
  In order to see what asset families I have
  As a user
  I want see a list of asset families

  @acceptance-front
  Scenario: List existing asset families
    Given the following asset families to list:
      | identifier |
      | designer   |
      | sofa       |
    When the user asks for the asset family list
    Then the user gets a selection of 2 items out of 2 items in total
    And the user gets an asset family "designer"
    And the user gets an asset family "sofa"

  @acceptance-front
  Scenario: Shows an empty list if there is no asset family
    When the user asks for the asset family list
    Then there is no asset family
