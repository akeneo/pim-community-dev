@javascript
Feature: Variant group creation
  In order to manage relations between products
  As a user
  I need to be able to manually create a variant group

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label      | type         |
      | color     | Color      | simpleselect |
      | size      | Size       | simpleselect |
      | dimension | Dimensions | simpleselect |
    And I am logged in as "admin"

  Scenario: Successfully display all required fields in the variant creation form
    Given I am on the variant groups page
    And I create a new variant group
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled

  Scenario: Successfully create a variant
    Given I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | MUG |
    And I select the axis "Color"
    And I press the "Save" button
    Then I am on the variant groups page
    And I should see groups MUG

  Scenario: Fail to create a variant missing the code
    Given I am on the variant groups page
    When I create a new variant group
    And I select the axis "Size"
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."

  Scenario: Fail to create a variant filling a non-valid code
    Given I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Group code may contain only letters, numbers and underscores."

  Scenario: Fail to create a variant filling an already used code
    Given the following product groups:
      | code   | label          | attributes  | type    |
      | TSHIRT | T-Shirt Akeneo | size, color | VARIANT |
    And I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | TSHIRT |
    And I press the "Save" button
    Then I should see validation error "This value is already used."

  Scenario: Fail to create a variant missing adding an axis
    Given I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | MUG |
    And I press the "Save" button
    Then I should see validation error "This collection should contain 1 element or more."
