Feature: Display the attribute history
  In order to know who, when and what changes has been made to an attribute
  As Julia
  I need to have access to a attribute history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Succesfully edit a attribute and see the history
    Given I am on the attribute creation page
    And I change the "Attribute type" to "Simple select"
    And I change the Code to "packaging"
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code        | Selected by default |
      | classic_box | yes                 |
    And I save the attribute
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property | value     |
      | create | 1       | code     | packaging |
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code      | Selected by default |
      | collector | no                  |
    And I save the attribute
    When I visit the "History" tab
    Then there should be 2 updates
