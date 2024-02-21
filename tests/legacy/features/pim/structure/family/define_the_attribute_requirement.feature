@javascript
Feature: Define the attribute requirement
  In order to ensure product completeness when exporting them
  As an administrator
  I need to be able to define which attributes are required or not for a given channel

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the "Boots" family page

  @critical
  Scenario: Successfully make an attribute required for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "rating" requirement in channel "mobile"
    And I save the family
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    Then attribute "rating" should be required in channels mobile and tablet

  Scenario: Successfully make an attribute optional for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "description" requirement in channel "tablet"
    And I save the family
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    Then attribute "description" should not be required in channels mobile and tablet
