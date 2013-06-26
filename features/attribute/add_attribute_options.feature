Feature: Add attribute options
  In order to define choices for a choice attribute
  As an user
  I need to add and remove options to attributes of type "Multi select" and "Simple select"

  @javascript
  Scenario: Sucessfully display the Options section when creating an attribute
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Simple select"
    Then I should see the "Options" section