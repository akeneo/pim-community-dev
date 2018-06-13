Feature: Display the sidebar
  In order to manage the edit form
  As a user
  I want to see the sidebar menu

  Background:
    Given the following enriched entities to list:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the following configured tabs:
      | code                                     |
      | pim-enriched-entity-edit-form-records    |
      | pim-enriched-entity-edit-form-attributes |
      | pim-enriched-entity-edit-form-properties |

  @acceptance-front
  Scenario: Display the sidebar with the tabs configured
    When the user asks for the enriched entity "designer"
    Then the user should see the sidebar with the configured tabs

#  @acceptance-front
  Scenario: Can collapse the sidebar
    When the user asks for the enriched entity "designer"
    And the user tries to collapse the sidebar
    Then the user should see the sidebar collapsed

#  @acceptance-front
  Scenario: Can display the properties view
    When the user asks for the enriched entity "designer"
    Then the user should see the properties view
