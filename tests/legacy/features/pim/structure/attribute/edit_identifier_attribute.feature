@javascript
Feature: Edit an identifier attribute
  In order to specify options for the identifier
  As a product manager
  I need to edit an identifier attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Fail to create a second identifier attribute
    Given I am on the attributes page
    When I create an "Identifier" attribute
    And I fill in the following information:
      | Code            | mySecondId |
      | Attribute group | Other      |
    And I press the "Save" button
    Then I should see the text "An identifier attribute already exists."
