@javascript
Feature: Create attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to create options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully create some attribute options
    Given I am on the attributes page
    And I create a "Simple select" attribute with code "size"
    And I fill in the following information:
      | Attribute group | Other |
    When I save the attribute
    And I visit the "Options" tab
    And I should not see the text "There are unsaved changes."
    And I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    When I switch the "Sort automatically options by alphabetical order" to "yes"
    Then I should see the text "green"
    When I save the attribute
    Then I should see the flash message "Attribute successfully updated"
    And I should see the text "green"
