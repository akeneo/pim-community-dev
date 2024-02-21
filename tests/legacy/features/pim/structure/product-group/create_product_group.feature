@javascript
Feature: Product group creation
  In order to manage relations between products
  As a product manager
  I need to be able to manually create a group

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the product groups page
    And I create a new product group
    Then I should see the Code and Type fields

  @critical
  Scenario: Successfully create a cross sell
    And I should not see the Axis field
    When I fill in the following information in the popin:
      | Code | Cross      |
      | Type | Cross sell |
    And I press the "Save" button
    And I should see the text "[Cross]"
    Then I am on the product groups page
    And I should see groups Cross
