@javascript
Feature: Ensure versioning on attribute
  In order to see old version of an existing attribute
  As a product manager
  I need to be able to view new versions after edition

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully version an attribute
    Given I am on the "comment" attribute page
    And I visit the "History" tab
    Then there should be 1 update
    Then I visit the "Label translations" tab
    When I fill in the following information:
      | English (United States) | My comment |
    And I press the "Save" button
    Then I visit the "History" tab
    Then there should be 2 update
