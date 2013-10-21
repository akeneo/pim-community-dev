@javascript
Feature: Variant group creation
  In order to add a non-imported variant group
  As a user
  I need to be able to manually create a variant group

  Background:
    Given the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And I am logged in as "admin"

  Scenario: Successfully display all required fields in the variant creation form
    Given I am on the variants page
    And I create a new variant
    Then I should see the Code and Axis fields

  @skip
  Scenario: Successfully create a variant
    Given I am on the variants page
    When I create a new variant
    And I fill in the following information:
      | Code | MUG     |
      | Axis | Color   |
    And I press the "Save" button
    Then I am on the variants page
    And I should see variant MUG

  @skip
  Scenario: Fail to create a variant missing the code
    Given I am on the variants page
    When I create a new variant
    And I fill in the following information:
      | Axis | Size    |
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."

  Scenario: Fail to create a variant filling a non-valid code
    Given I am on the variants page
    When I create a new variant
    And I fill in the following information:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Variant group code may contain only letters, numbers and underscores."

  Scenario: Fail to create a variant filling an already used code
    Given I am on the variants page
    And the following variants:
      | code    | label          | attributes  | type    |
      | TSHIRT  | T-Shirt Akeneo | size, color | VARIANT |
    When I create a new variant
    And I fill in the following information:
      | Code | TSHIRT  |
    And I press the "Save" button
    Then I should see validation error "This value is already used."

  Scenario: Fail to create a variant missing adding an axis
    Given I am on the variants page
    When I create a new variant
    And I fill in the following information:
      | Code | MUG     |
    And I press the "Save" button
    Then I should see validation error "This collection should contain 1 element or more."
