@javascript
Feature: Filter product drafts
  In order to easily find product drafts for the product
  As a product manager
  I need to be able to filter them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following product drafts:
      | product     | status      | author |
      | black-boots | in progress | Sandra |
      | black-boots | ready       | Mary   |
      | white-boots | ready       | Sandra |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully filter product drafts
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And I should see entities Sandra and Mary
    And I should be able to use the following filters:
      | filter | value                | result |
      | Status | In progress          | Sandra |
      | Status | Waiting for approval | Mary   |
