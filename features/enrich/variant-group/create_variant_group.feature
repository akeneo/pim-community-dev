@javascript @skip
Feature: Variant group creation
  In order to manage relations between products
  As a product manager
  I need to be able to manually create a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the variant groups page
    And I create a new variant group

  Scenario: Successfully create a variant group
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    And I fill in the following information in the popin:
      | Code | MUG   |
      | Axis | Color |
    And I press the "Save" button in the popin
    Then I am on the variant groups page
    And I should see group MUG

  Scenario: Fail to create a variant group with an empty or invalid code
    Given I fill in the following information in the popin:
      | Axis | Size |
    And I press the "Save" button in the popin
    Then I should see validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button in the popin
    Then I should see validation error "Group code may contain only letters, numbers and underscores."

  Scenario: Fail to create a variant group with an already used code
    Given I fill in the following information in the popin:
      | Code | caterpillar_boots |
    And I press the "Save" button
    Then I should see validation error "This value is already used."

  Scenario: Fail to create a variant group without an axis
    Given I fill in the following information in the popin:
      | Code | MUG |
    And I press the "Save" button
    Then I should see the text "Variant group \"MUG\" must be defined with at least one axis"

  Scenario: Successfully create a variant group and check history
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    And I fill in the following information in the popin:
      | Code | MUG   |
      | Axis | Color |
    And I press the "Save" button
    And I visit the "History" tab
    And I should see history:
      | version | author                          | property | value |
      | 1       | Julia Stark - Julia@example.com | code     | MUG   |
    Then I am on the variant groups page
    And I should see group MUG
