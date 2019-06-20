Feature: Display the sidebar
  In order to manage the edit form
  As a user
  I want to see the sidebar menu

  Background:
    Given the following asset families to list:
      | identifier | labels                                       | permission     |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | {"edit": true} |
    And the following configured tabs:
      | code       |
      | asset     |
      | attribute  |
      | property   |
      | permission |

  @acceptance-front
  Scenario: Display the sidebar with the tabs configured
    When the user asks for the asset family "designer"
    Then the user should see the sidebar with the configured tabs

  @acceptance-front
  Scenario: Can collapse the sidebar
    When the user asks for the asset family "designer"
    And the user tries to collapse the sidebar
    Then the user should see the sidebar collapsed

  @acceptance-front
  Scenario: Can display the active tab view
    When the user asks for the asset family "designer"
    Then the user should see the active tab view
