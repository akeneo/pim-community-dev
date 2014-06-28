@javascript
Feature: Define the attribute requirement
  In order to ensure product completness when exporting them
  As an administrator
  I need to be able to define which attributes are required or not for a given channel

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the "Boots" family page

  Scenario: Succesfully display the attribute requirements
    Given I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    And attribute "lace_color" should not be required in channels mobile and tablet
    And attribute "side_view" should be required in channel tablet
    And attribute "side_view" should not be required in channel mobile

  Scenario: Succesfully make an attribute required for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    And I visit the "Attributes" tab
    Then attribute "rating" should be required in channels mobile and tablet

  Scenario: Succesfully make an attribute optional for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "Description" requirement in channel "Tablet"
    And I save the family
    And I visit the "Attributes" tab
    Then attribute "description" should not be required in channels mobile and tablet
