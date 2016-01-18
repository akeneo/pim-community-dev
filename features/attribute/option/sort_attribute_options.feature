@javascript
Feature: Sortd attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to sort options for attributes of type "Multi select" and "Simple select"

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

  Scenario: Auto sorting disable reorder
    Given I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    Then I should not see reorder handles
    Given I uncheck the "Automatic option sorting" switch
    And I wait for options to load
    Then I should see reorder handles
