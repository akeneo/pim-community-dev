@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to add and remove options for attributes of type "Multi select" and "Simple select"

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

  Scenario: Successfully edit some attribute options
    Given I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    And I wait for options to load
    And I edit the "green" option and turn it to "yellow"
    Then I should see "yellow"
    Then I should not see "green"

  Scenario: Successfully cancel while editing some attribute options
    Given I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    And I wait for options to load
    And I edit the code "green" to turn it to "yellow" and cancel
    Then I should see a confirm dialog with the following content:
      | title   | Cancel modification                                                                                    |
      | content | Warning, you will lose unsaved data. Are you sure you want to cancel modification on this new option ? |
    And I confirm the cancellation
    And I wait for options to load
    Then I should see "green"
    But I should not see "yellow"

  @jira https://akeneo.atlassian.net/browse/PIM-2166
  Scenario: Successfully delete some attribute options
    Given I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    When I remove the "small_size" option
    And I confirm the deletion
    And I wait for options to load
    Then I should not see "small_size"

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
