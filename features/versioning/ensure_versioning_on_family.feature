Feature: Ensure versioning on family
  In order to see old version of an existing family
  As a product manager
  I need to be able to view new versions after edition

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully version a family
    Given I am on the "heels" family page
    And I visit the "History" tab
    Then there should be 1 update
    Then I visit the "Properties" tab
    When I fill in the following information:
      | English (United States) | My heels |
    And I press the "Save" button
    Then I visit the "History" tab
    Then there should be 2 update
