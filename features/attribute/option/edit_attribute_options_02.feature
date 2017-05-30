@javascript
Feature: Edit attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to edit options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Simple select" attribute
    And I scroll down
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I visit the "Values" tab
    Then I should see the "Options" section
    Then I should see "To manage options, please save the attribute first"
    And I save the attribute
    Then I should see the flash message "Attribute successfully created"
    And I check the "Automatic option sorting" switch

  Scenario: Successfully cancel while editing some attribute options
    Given I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    And I edit the attribute option "green" to turn it to "yellow" and cancel
    Then I should see a confirm dialog with the following content:
      | title   | Cancel modification                                                                                    |
      | content | Warning, you will lose unsaved data. Are you sure you want to cancel modification on this new option ? |
    And I confirm the cancellation
    Then I should see "green"
    But I should not see "yellow"

  @jira https://akeneo.atlassian.net/browse/PIM-6002
  Scenario: Successfully edit an attribute option value containing a quote
    Given I create the following attribute options:
      | Code  | en_US |
      | red   | r"ed  |
      | blue  | blue  |
      | green | green |
    And I save the attribute
    And I should not see the text "There are unsaved changes."
    And I edit the attribute option "red" to turn it to "red" and cancel
    Then I should not see the text "r\"ed"
