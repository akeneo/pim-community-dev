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

  Scenario: Successfully create and validate a text attribute
    Given I fill in the following information:
      | Code            | short_description |
      | Attribute group | Other             |
    And I save the attribute
    Then I should see the flash message "Attribute successfully created"

  @jira https://akeneo.atlassian.net/browse/PIM-6324
  Scenario: Successfully switch to tab with an invalid field
    Given I visit the "Label translations" tab
    And I save the attribute
    Then I should see the Code field
    And I should be on the "Properties" tab
    And I should see validation error "This value should not be blank."
