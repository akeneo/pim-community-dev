Feature: Display the sidebar
  In order to manage the edit form
  As a user
  I want to see the sidebar menu

  Background:
    Given the following reference entities to list:
      | identifier | labels                                       | permission     |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | {"edit": true} |
    And the following configured tabs:
      | code       |
      | record     |
      | attribute  |
      | property   |
      | permission |

  @acceptance-front
  Scenario: Display the sidebar with the tabs configured
    When the user asks for the reference entity "designer"
    Then the user should see the sidebar with the configured tabs

  @acceptance-front
  Scenario: Can collapse the sidebar
    When the user asks for the reference entity "designer"
    And the user tries to collapse the sidebar
    Then the user should see the sidebar collapsed

  @acceptance-front
  Scenario: Can display the active tab view
    When the user asks for the reference entity "designer"
    Then the user should see the active tab view
