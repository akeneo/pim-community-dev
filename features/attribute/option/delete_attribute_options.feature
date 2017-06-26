@javascript
Feature: Delete attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to delete options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I save the attribute
    When I visit the "Options" tab
    Then I should see the flash message "Attribute successfully created"
    And I check the "Sort automatically options by alphabetical order" switch

  @jira https://akeneo.atlassian.net/browse/PIM-2166
  Scenario: Successfully delete some attribute options
    Given I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    When I remove the "small_size" option
    And I confirm the deletion
    Then I should not see the text "small_size"
