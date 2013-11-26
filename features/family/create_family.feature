Feature: Family creation
  In order to provide a new family for a new type of product
  As a user
  I need to be able to create a family

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the family creation page

  Scenario: Succesfully create a family
    Given I change the Code to "running_shoes"
    And I save the family
    Then I should see "Family successfully created"
    And I should be on the "running_shoes" family page

  Scenario: Fail to set an already used code
    Given I change the Code to "sandals"
    And I save the family
    Then I should see a tooltip "This value is already used."

  Scenario: Fail to set a non-valid code
    Given I change the Code to "***"
    And I save the family
    Then I should see a tooltip "Family code may contain only letters, numbers and underscores"
