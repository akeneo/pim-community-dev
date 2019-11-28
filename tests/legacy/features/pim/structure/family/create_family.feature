@javascript
Feature: Family creation
  In order to provide a new family for a new type of product
  As an administrator
  I need to be able to create a family

  Background:
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the families grid
    And I create a new family

  @critical
  Scenario: Successfully create a family
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | CAR |
    And I press the "Save" button
    Then I should be redirected to the "CAR" family page
    And I should see the text "Family successfully created"
    And I should see the text "[CAR]"
