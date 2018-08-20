@javascript
Feature: Display the attribute history
  In order to know who, when and what changes has been made to an attribute
  As a product manager
  I need to have access to a attribute history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attribute group:
      | code      | label-en_US |
      | technical | Technical   |

  Scenario: Successfully edit a reference data attribute and see the history
    Given I am on the attributes page
    And I create a "Reference data simple select" attribute
    And I fill in the following information:
      | Code                | mycolor |
      | Reference data type | color   |
      | Attribute group     | Other   |
    And I save the attribute
    Then I should see the flash message "Attribute successfully created"
    And I should not see the text "There are unsaved changes."
    When I change the "Attribute group" to "Technical"
    And I save the attribute
    Then I should see the flash message "Attribute successfully updated"
    And I should not see the text "There are unsaved changes."
    When I visit the "History" tab
    Then there should be 2 update
    And I should see history:
      | version | property | value     | date |
      | 2       | group    | technical | now  |
