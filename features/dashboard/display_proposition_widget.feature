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

  Scenario: Successfully display all propositions that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-jacket    | jackets | jackets    |
      | my-tee-shirt | tees    | tees       |
    And the following propositions:
      | product      | author | status      |
      | my-jacket    | mary   | ready       |
      | my-tee-shirt | mary   | ready       |
      | my-jacket    | john   | in progress |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Propositions to review"
    And I should the following proposition:
      | product   | author |
      | my-jacket | mary   |

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "clothing" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see "Propositions to review"
