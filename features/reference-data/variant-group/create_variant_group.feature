@javascript
Feature: Variant group creation with simple reference data
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
      | Code | Boots      |
      | Axis | Sole color |
    And I press the "Save" button
    Then I am on the variant groups page
    And I should see group Boots

  Scenario: Fail to create a variant group with multiple reference data
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    Then the "Axis" field should not contain "Sole fabric"
