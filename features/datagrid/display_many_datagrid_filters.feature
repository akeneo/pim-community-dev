@javascript
Feature: Display many datagrid filters
  In order to be able to have many filterable attributes
  As a regular user
  I need to be able to use the datagrid with many filters

  @jira https://akeneo.atlassian.net/browse/PIM-5536
  Scenario: Successfully search an attribute from its code
    Given the "apparel" catalog configuration
    And I am logged in as "Mary"
    And I am on the products page
    When I type "number_in_stock" in the manage filter input
    And I could see "Number in stock" in the manage filters list

