@javascript
Feature: Display the product model history
  In order to know by who, when and what changes have been made to a product model
  As a product manager
  I need to have access to a product model history

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Display product model updates
    Given I am on the "amor" product model page
    When I visit the "History" column tab
    Then there should be 1 update
    When I visit the "Attributes" column tab
    And I change the "Price" to "999 USD"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I visit the "History" column tab
    Then there should be 2 update
    And I should see history:
      | version | property  | value   | date |
      | 2       | Price USD | $999.00 | now  |
