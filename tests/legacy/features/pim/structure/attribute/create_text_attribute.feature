@javascript
Feature: Create an attribute
  In order to be able to define the properties of a product
  As a product manager
  I need to create a text attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Text" attribute

  @critical
  Scenario: Successfully create and validate a text attribute
    Given I fill in the following information:
      | Code            | short_description |
      | Attribute group | Other             |
    And I save the attribute
    Then I should see the flash message "Attribute successfully created"
