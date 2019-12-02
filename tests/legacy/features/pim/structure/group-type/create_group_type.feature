@javascript
Feature: Group type creation
  In order to create a new type of group
  As an administrator
  I need to be able to manually create a group type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the group types page
    And I create a new group type

  Scenario: Successfully create a group type
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | special |
    And I press the "Save" button
    Then I should see the text "Group type successfully created"
    And I wait 5 seconds
    Then I should be on the "special" group type page
    And I should see the text "special"
