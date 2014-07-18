@javascript
Feature: Display proposition widget
  In order to easily see which products have pending propositions
  As a product manager
  I need to be able to see a widget with pending propositions on the dashboard

  Scenario: Display proposition widget
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Propositions to review"
    And I should see "No propositions to review"

  Scenario: Successfully display all opened propositions
    Given the "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And the following propositions:
      | product    | author | status      |
      | my-sandals | mary   | ready       |
      | my-sandals | john   | in progress |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Propositions to review"
    And I should the following proposition:
      | product    | author |
      | my-sandals | mary   |

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "clothing" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see "Propositions to review"
