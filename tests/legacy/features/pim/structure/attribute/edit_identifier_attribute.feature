@javascript
Feature: Edit an identifier attribute
  In order to specify options for the identifier
  As a product manager
  I need to edit an identifier attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Success to create an second identifier attribute
    Given I am on the attributes page
    When I create an "Identifier" attribute with code "mySecondId"
    And I fill in the following information:
      | Attribute group | Other      |
    And I press the "Save" button
    Then I should see the text "Attribute successfully updated"
