@javascript
Feature: Filter product drafts
  In order to easily find product drafts for the product
  As a product manager
  I need to be able to filter them

  Background:
    Given an "apparel" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2015_collection  | Redactor   | edit   |
      | 2015_collection  | Manager    | edit   |
      | 2015_collection  | IT support | own    |
    And the following products:
      | sku    | family  | categories      |
      | tshirt | tshirts | 2015_collection |
    And the following product drafts:
      | product | status      | author | result                                                                    |
      | tshirt  | in progress | Sandra | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
    And Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And Julia proposed the following change to "tshirt":
      | field | value         | tab     |
      | Name  | Autumn jacket | General |
      | Price | 10 USD        | Sales   |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario Outline: Successfully filter product drafts
    Given I am logged in as "Peter"
    And I edit the "tshirt" product
    When I visit the "Proposals" column tab
    Then the grid should contain 3 elements
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter    | value | result              | count |
      | attribute | Name  | Mary, Julia, Sandra | 3     |
      | attribute | Price | Julia               | 1     |
