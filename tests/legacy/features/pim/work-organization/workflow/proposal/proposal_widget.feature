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
      | product      | source | source_label | author | author_label  | status | result                                                                    |
      | my-jacket    | pim    | PIM          | mary   | Mary Smith    | ready  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | my-jacket    | pim    | PIM          | sandra | Sandra Harvey | ready  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
      | my-tee-shirt | pim    | PIM          | sandra | Sandra Harvey | ready  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change3"}]}} |
    And I am logged in as "Julia"

  Scenario: Successfully get redirected to correct Sandra's proposal grid filtered on selected proposal
    When I am on the dashboard page
    And I click on the proposal to review created by "Sandra" on the product "my-tee-shirt"
    Then I should be on the proposals index page
    And the grid should contain 1 elements
    And I should see entity my-tee-shirt
    And I should see the text "Sandra"

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

  @jira https://akeneo.atlassian.net/browse/PIM-5934
  Scenario: Successfully go to the proposal view
    Given I am on the dashboard page
    When I follow "View all proposals"
    Then I should be on the proposals index page
