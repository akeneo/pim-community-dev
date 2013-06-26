@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As an user
  I need to add and remove options to attributes of type "Multi select" and "Simple select"

  Scenario Outline: Sucessfully display the Options section when creating an attribute
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "<type>"
    Then I should see the "Options" section
    And the Options section should contain 1 empty option

    Examples:
      | type          |
      | Simple select |
      | Multi select  |

  Scenario: Fail to remove the only option
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Simple select"
    Then the option should not be removable
