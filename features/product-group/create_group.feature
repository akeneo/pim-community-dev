@javascript
Feature: Product group creation
  In order to manage relations between products
  As a user
  I need to be able to manually create a group

  Background:
    Given the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And I am logged in as "admin"

  Scenario: Successfully display all required fields in the variant creation form
    Given I am on the product groups page
    And I create a new product group
    Then I should see the Code and Axis fields

  Scenario: Successfully create a variant
    Given I am on the product groups page
    When I create a new product group
    And I fill in the following information:
      | Code | MUG     |
    And I select the axis "Color"
    And I press the "Save" button
    Then I am on the product groups page
    And I should see group MUG

  Scenario: Successfully create a cross sell
    Given I am on the product groups page
    When I create a new product group
    And I fill in the following information:
      | Code | Cross  |
      | Type | X_SELL |
    And I press the "Save" button
    Then I am on the product groups page
    And I should see group Cross

  Scenario: Fail to create a variant missing the code
    Given I am on the product groups page
    When I create a new product group
    And I select the axis "Size"
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."

  Scenario: Fail to create a variant filling a non-valid code
    Given I am on the product groups page
    When I create a new product group
    And I fill in the following information:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Group code may contain only letters, numbers and underscores."

  Scenario: Fail to create a variant filling an already used code
    Given I am on the product groups page
    And the following product groups:
      | code    | label          | attributes  | type    |
      | TSHIRT  | T-Shirt Akeneo | size, color | VARIANT |
    When I create a new product group
    And I fill in the following information:
      | Code | TSHIRT  |
    And I press the "Save" button
    Then I should see validation error "This value is already used."

  Scenario: Fail to create a variant missing adding an axis
    Given I am on the product groups page
    When I create a new product group
    And I fill in the following information:
      | Code | MUG     |
    And I press the "Save" button
    Then I should see validation error "This collection should contain 1 element or more."
