@javascript
Feature: Group type creation
  In order to create a new type of group
  As a user
  I need to be able to manually create a group type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully create a group type
    Given I am on the group types page
    When I create a new group type
    Then I should see the Code field
    And I fill in the following information in the popin:
      | Code | special |
    And I press the "Save" button
    Then I should be on the "special" group type page
    And I should see "Edit group type - [special]"

  Scenario: Fail to create a group type with an empty or invalid code
    Given I am on the group types page
    When I create a new group type
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."
    And I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Group type code may contain only letters, numbers and underscores."

  Scenario: Fail to create a group type with an already used code
    Given the following group types:
      | code    |
      | special |
    When I am on the group types page
    And I create a new group type
    And I fill in the following information in the popin:
      | Code | special |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
