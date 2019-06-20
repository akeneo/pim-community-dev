Feature: Show asset family
  In order to see the details of an asset family
  As a user
  I want see the details of an asset family

  @acceptance-front
  Scenario: Getting a single entity
    Given the following asset families to show:
      | identifier | labels                                       | permission     |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | {"edit": true} |
    When the user asks for the asset family "designer"
    And the user gets the asset family "designer" with label "Designer"
