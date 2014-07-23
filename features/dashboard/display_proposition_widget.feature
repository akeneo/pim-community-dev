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

  Scenario: Successfully display new propositions that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-tee-shirt | tees    | tees       |
    And the following propositions:
      | product      | author | status      |
      | my-tee-shirt | mary   | ready       |
    And I am logged in as "Peter"
    When I edit the "my-tee-shirt" product
    And I visit the "Categories" tab
    And I select the "2014 collection" tree
    And I expand the "2014 collection" category
    And I expand the "Summer collection" category
    And I click on the "Jackets" category
    And I press the "Save" button
    And I logout
    And I am logged in as "Julia"
    And I go to the dashboard page
    Then I should see "Propositions to review"
    And I should the following proposition:
      | product      | author |
      | my-tee-shirt | mary   |

  Scenario: Successfully hide propositions belonging to the last category I was owner of that was removed
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-jacket    | jackets | jackets    |
    And the following propositions:
      | product      | author | status      |
      | my-jacket    | mary   | ready       |
      | my-jacket    | john   | in progress |
    And I am logged in as "Peter"
    When I am on the "jackets" category page
    When I press the "Delete" button
    And I confirm the deletion
    And I logout
    And I am logged in as "Julia"
    When I go to the dashboard page
    Then I should see "Propositions to review"
    And I should see "No propositions to review"

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "clothing" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see "Propositions to review"
