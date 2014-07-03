@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to add and remove options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario Outline: Sucessfully display the Options section when creating an attribute
    Given I create a "<type>" attribute
    And I visit the "Values" tab
    Then I should see the "Options" section
    And the Options section should contain 1 empty option

    Examples:
      | type          |
      | Simple select |
      | Multi select  |

  Scenario: Fail to remove the only option
    Given I create a "Simple select" attribute
    And I visit the "Values" tab
    Then the Options section should contain 1 empty option
    And the option should not be removable

  Scenario: Fail to create a select attribute with an empty option
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | color |
      | Attribute group | Other |
    And I save the attribute
    Then I should see "Code must be specified for all options"

  Scenario: Successfully create a select attribute with some options
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | color |
      | Attribute group | Other |
    And I visit the "Values" tab
    And I check the "Automatic option sorting" switch
    And I create the following attribute options:
      | Code  | Selected by default |
      | red   | no                  |
      | blue  | yes                 |
      | green | no                  |
    And I save the attribute
    Then I should see flash message "Attribute successfully created"

  @jira https://akeneo.atlassian.net/browse/PIM-2166
  Scenario: Remove some options
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code | Selected by default |
      | S    | no                  |
      | M    | yes                 |
      | L    | no                  |
    And I save the attribute
    And I should see flash message "Attribute successfully created"
    When I remove the "S" option
    And I save the attribute
    Then I should see flash message "Attribute successfully updated"
