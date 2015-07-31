Feature: Ensure versioning on channel
  In order to see old version of an existing channel
  As a product manager
  I need to be able to view new versions after edition

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully version a channel
    Given I am on the "mobile" channel page
    And I visit the "History" tab
    Then there should be 1 update
    Then I visit the "Properties" tab
    When I fill in the following information:
      | Default label | My mobile |
    And I press the "Save" button
    Then I visit the "History" tab
    Then there should be 2 update

  # will be fixed in PIM-4641
  @javascript @skip
  Scenario: Successfully version a channel on currency disabling
    Given I am on the "tablet" channel page
    And I visit the "History" tab
    Then there should be 1 update
    Then I am on the currencies page
    Given I filter by "Activated" with value "yes"
    When I deactivate the USD currency
    Given I am on the "tablet" channel page
    Then I visit the "History" tab
    Then there should be 2 update

