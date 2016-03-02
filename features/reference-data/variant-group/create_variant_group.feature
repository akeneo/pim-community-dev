@javascript
Feature: Variant group creation with simple reference data
  In order to manage relations between products
  As a product manager
  I need to be able to manually create a variant group

  Background:
    Given a "footwear" catalog configuration

  Scenario: Successfully create a variant group
    And I am logged in as "Julia"
    And I am on the variant groups page
    And I create a new variant group
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    And I fill in the following information in the popin:
      | Code | Boots      |
      | Axis | Sole color |
    And I press the "Save" button
    Then I should be on the "Boots" variant group page
    Then I am on the variant groups page
    And I should see group Boots

  Scenario: Fail to create a variant group with multiple reference data
    And I am logged in as "Julia"
    And I am on the variant groups page
    And I create a new variant group
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    Then the "Axis" field should not contain "Sole fabric"

  @jira https://akeneo.atlassian.net/browse/PIM-4783
  Scenario: Successfully create a variant group and see linked products
    And the following "sole_color" attribute reference data: Red and Green
    And the following product:
      | sku  | sole_color |
      | boot | Red        |
    And I am logged in as "Julia"
    And I am on the variant groups page
    And I create a new variant group
    Then I should see the Code, Axis and Type fields
    And the field Type should be disabled
    And I fill in the following information in the popin:
      | Code | Boots      |
      | Axis | Sole color |
    And I press the "Save" button
    Then I should be on the "Boots" variant group page
    And I should see products boot
    When I am on the variant groups page
    Then I should see group Boots
