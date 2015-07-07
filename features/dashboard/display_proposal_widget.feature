Feature: Display proposal widget
  In order to easily see which products have pending proposals
  As a product manager
  I need to be able to see a widget with pending proposals on the dashboard

  @javascript
  Scenario: Display proposal widget
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Proposals to review"
    When I wait for widgets to load
    Then I should see "No proposals to review"

  @javascript
  Scenario: Successfully display all proposals that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-jacket    | jackets | jackets    |
      | my-tee-shirt | tees    | tees       |
    And the following product drafts:
      | product      | author | status      |
      | my-jacket    | mary   | ready       |
      | my-tee-shirt | mary   | ready       |
      | my-jacket    | john   | in progress |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Proposals to review"
    When I wait for widgets to load
    Then I should see the following proposals:
      | product   | author     |
      | my-jacket | Mary Smith |

  @javascript
  Scenario: Successfully display new proposals that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family | categories |
      | my-tee-shirt | tees   | tees       |
    And the following product drafts:
      | product      | author | status |
      | my-tee-shirt | mary   | ready  |
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
    Then I should see "Proposals to review"
    When I wait for widgets to load
    Then I should see the following proposals:
      | product      | author     |
      | my-tee-shirt | Mary Smith |

  @javascript
  Scenario: Successfully hide proposals belonging to the last category I was owner of that was removed
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | categories |
      | my-jacket | jackets | jackets    |
    And the following product drafts:
      | product   | author | status      |
      | my-jacket | mary   | ready       |
      | my-jacket | john   | in progress |
    And I am logged in as "Peter"
    When I am on the "jackets" category page
    When I press the "Delete" button
    And I confirm the deletion
    And I logout
    And I am logged in as "Julia"
    When I go to the dashboard page
    Then I should see "Proposals to review"
    When I wait for widgets to load
    Then I should see "No proposals to review"

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "default" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see "Proposals to review"
