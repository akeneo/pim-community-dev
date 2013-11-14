@javascript
Feature: Association creation
  In order to create a new type of association
  As a user
  I need to be able to manually create an association

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully create an association
    Given I am on the associations page
    When I create a new association
    Then I should see the Code field
    And I fill in the following information in the popin:
      | Code | up_sell |
    And I press the "Save" button
    Then I should be on the "up_sell" association page
    And I should see "Edit association - [up_sell]"

  Scenario: Fail to create an association with an empty or invalid code
    Given I am on the associations page
    When I create a new association
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."
    And I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Association code may contain only letters, numbers and underscores"

  Scenario: Fail to create an association with an already used code
    Given the following association:
      | code       |
      | cross_sell |
    When I am on the associations page
    And I create a new association
    And I fill in the following information in the popin:
      | Code | cross_sell |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
