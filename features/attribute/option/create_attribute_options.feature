@javascript
Feature: Create attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to create options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I visit the "Values" tab
    Then I should see the "Options" section
    Then I should see "To manage options, please save the attribute first"
    And I save the attribute
    Then I should see flash message "Attribute successfully created"
    And I wait for options to load
    And I check the "Automatic option sorting" switch

  Scenario: Successfully create some attribute options
    Given I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    Then I should see "green"
    And I save the attribute
    And I wait for options to load
    Then I should see flash message "Attribute successfully updated"
    Then I should see "green"
