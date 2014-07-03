@javascript
Feature: Browse currencies
  In order to check wether or not a currency is available in the catalog
  As an administrator
  I need to be able to see active and inactive currencies in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the currencies page

  Scenario: Successfully view, sort and filter currencies
    Then I should see the columns Code and Activated
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code and activated
    And I should be able to use the following filters:
      | filter    | value | result      |
      | Code      | EU    | EUR         |
      | Activated | yes   | USD and EUR |

  Scenario: Successfully activate a currency
    Given I filter by "Code" with value "GBP"
    And I activate the GBP currency
    When I hide the filter "Code"
    And I filter by "Activated" with value "yes"
    Then the grid should contain 3 elements
    Then I should see currencies GBP, USD and EUR

  Scenario: Successfully deactivate a currency
    Given I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR
    When I deactivate the USD currency
    Then the grid should contain 1 element
    And I should see currency EUR
