@javascript
Feature: Display the attribute history
  In order to know who, when and what changes has been made to an attribute
  As a product manager
  I need to have access to a attribute history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit a attribute and see the history
    Given I am on the attributes page
    And I create a "Simple select" attribute
    And I change the "Code" to "packaging"
    And I change the "Attribute group" to "Other"
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code        |
      | classic_box |
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    When I visit the "History" tab
    Then there should be 2 update
    And I should see history:
      | version | property | value     |
      | 1       | code     | packaging |
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code      |
      | collector |
    And I save the attribute
    Then I should not see the text "There are unsaved change"
    When I visit the "History" tab
    Then there should be 3 updates
