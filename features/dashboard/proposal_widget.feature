@javascript
Feature: Display proposal widget
  In order to easily see which products have pending proposals
  As a product manager
  I need to be able to see a widget with pending proposals on the dashboard

  Background:
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

  Scenario: Successfully get redirected to correct Sandra's proposal grid filtered on selected proposal
    When I am on the dashboard page
    And I click on the proposal to review created by "Sandra" on the product "my-tee-shirt"
    Then I should be on the proposals index page
    And the grid should contain 1 elements
    And I should see entity my-tee-shirt

  Scenario: Successfully get redirected to correct Mary's proposal grid filtered on selected proposal
    When I am on the dashboard page
    And I click on the proposal to review created by "Mary" on the product "my-jacket"
    Then I should be on the proposals index page
    And the grid should contain 1 elements
    And I should see entity my-jacket
    And I should see the text "Mary"

  Scenario: Successfully get redirected to correct Sandra's proposal grid filtered on selected proposal
    When I am on the dashboard page
    And I click on the proposal to review created by "Sandra" on the product "my-jacket"
    Then I should be on the proposals index page
    And the grid should contain 1 elements
    And I should see entity my-jacket
    And I should see the text "Sandra"
