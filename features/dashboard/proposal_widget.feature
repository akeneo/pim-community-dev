Feature: Display proposal widget
  In order to easily see which products have pending proposals
  As a product manager
  I need to be able to see a widget with pending proposals on the dashboard

  @javascript
  Scenario: Successfully get redirected on proposal grid filtered on product I want to review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-jacket    | jackets | jackets    |
      | my-tee-shirt | jackets | jackets    |
    And the following product drafts:
      | product      | author | status |
      | my-jacket    | mary   | ready  |
      | my-jacket    | sandra | ready  |
      | my-tee-shirt | sandra | ready  |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I wait for widgets to load
    And I click on the proposal to review created by "Sandra" on the product "my-tee-shirt"
    Then I should be on the proposals index page
    Then the grid should contain 1 elements
    And I should see entity my-tee-shirt
    When I am on the dashboard page
    Then I wait for widgets to load
    And I click on the proposal to review created by "Mary" on the product "my-jacket"
    Then I should be on the proposals index page
    Then the grid should contain 1 elements
    And I should see entity my-jacket
    And I should see the text "Mary"
    When I am on the dashboard page
    Then I wait for widgets to load
    And I click on the proposal to review created by "Sandra" on the product "my-jacket"
    Then I should be on the proposals index page
    Then the grid should contain 1 elements
    And I should see entity my-jacket
    And I should see the text "Sandra"
