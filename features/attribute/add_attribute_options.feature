@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a user
  I need to add and remove options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the attribute creation page

  Scenario Outline: Sucessfully display the Options section when creating an attribute
    Given I select the attribute type "<type>"
    And I visit the "Values" tab
    Then I should see the "Options" section
    And the Options section should contain 1 empty option

    Examples:
      | type          |
      | Simple select |
      | Multi select  |

  Scenario: Fail to remove the only option
    Given I select the attribute type "Simple select"
    And I visit the "Values" tab
    Then the Options section should contain 1 empty option
    And the option should not be removable

  Scenario: Fail to create a select attribute with an empty option
    Given I select the attribute type "Simple select"
    And I fill in the following information:
     | Code | color |
    And I save the attribute
    Then I should see "Code must be specified for all options"

  Scenario: Successfully create a select attribute with some options
    Given I select the attribute type "Simple select"
    And I fill in the following information:
     | Code | color |
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code  | Selected by default |
      | red   | no                  |
      | blue  | yes                 |
      | green | no                  |
    And I save the attribute
    Then I should see flash message "Attribute successfully created"
