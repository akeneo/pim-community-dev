@javascript
Feature: Ensure versioning on attribute group
  In order to see old version of an existing attribute group
  As a product manager
  I need to be able to view new versions after edition

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully version an attribute group
    Given I am on the "sizes" attribute group page
    And I visit the "History" tab
    Then there should be 2 update
    Then I visit the "Properties" tab
    When I fill in the following information:
      | English (United States) | My sizes |
    And I press the "Save" button
    Then I visit the "History" tab
    Then there should be 3 update
