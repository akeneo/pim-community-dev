@javascript
Feature: Product group creation
  In order to manage relations between products
  As a user
  I need to be able to manually create a group

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully create a cross sell
    Given I am on the product groups page
    When I create a new product group
    Then I should see the Code and Type fields
    And I should not see the Axis field
    When I fill in the following information in the popin:
      | Code | Cross |
    And I select "X_SELL" from "Type"
    And I press the "Save" button
    Then I am on the product groups page
    And I should see groups Cross

  Scenario: Fail to create a group with a missing, invalid or already used code
    Given the following product group:
      | code   | label          | type   |
      | TSHIRT | T-Shirt Akeneo | X_SELL |
    And I am on the product groups page
    When I create a new product group
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Group code may contain only letters, numbers and underscores."
    When I fill in the following information in the popin:
      | Code | TSHIRT |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
