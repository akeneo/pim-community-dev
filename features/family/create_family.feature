@javascript
Feature: Family creation
  In order to provide a new family for a new type of product
  As an administrator
  I need to be able to create a family

  Background:
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the families page
    And I create a new family

  Scenario: Succesfully create a family
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | CAR |
    And I press the "Save" button
    Then I should be on the "CAR" family page
    And I should see "Edit family - [CAR]"

  Scenario: Fail to create a family with an empty or invalid code
    Given I press the "Save" button
    Then I should see validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Family code may contain only letters, numbers and underscores"

  Scenario: Fail to create a family with an already used code
    Given the following family:
      | code |
      | BOAT |
    When I fill in the following information in the popin:
      | Code | BOAT |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
