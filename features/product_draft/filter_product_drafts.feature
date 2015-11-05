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
    And the following products:
      | sku     | family   | categories      |
      | tshirt  | tshirts  | 2015_collection |
    And the following product drafts:
      | product | status      | author |
      | tshirt  | in progress | Sandra |
    And Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And Julia proposed the following change to "tshirt":
      | field       | value         | tab     |
      | Name        | Autumn jacket | General |
      | Price       | 10 USD        | Sales   |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully filter product drafts
    Given I am logged in as "admin"
    And I edit the "tshirt" product
    When I visit the "Proposals" tab
    Then the grid should contain 3 elements
    And I should be able to use the following filters:
      | filter    | value                | result      |
      | Status    | In progress          | Sandra      |
      | Status    | Waiting for approval | Mary, Julia |
      | Attribute | Name                 | Mary, Julia |
      | Attribute | Price                | Julia       |
