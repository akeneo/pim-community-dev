@javascript
Feature: Approve proposals in datagrid and reload page when delete last
  In order to cleanly use filters on proposals
  As a product manager
  The page should reload when deleting the last page proposal

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku        | family  | categories        | price         |
      | my-jacket  | jackets | winter_top        | 45 USD,75 EUR |
      | full-metal | jackets | summer_collection | 45 USD,75 EUR |
    And Mary proposed the following change to "my-jacket":
      | field | value     | tab                 |
      | Name  | My jacket | Product information |
      | Price | 5 USD     | Marketing           |
    And Sandra proposed the following change to "full-metal":
      | field | value  | tab       |
      | Price | 30 USD | Marketing |
    And I am logged in as "Julia"
    And I am on the proposals page
    And I filter by "author" with operator "" and value "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-5448
  Scenario: Successfully filter proposals and approve them
    Given I click on the "Approve all" action of the row which contains "my-jacket"
    And I press the "Send" button in the popin
    Then the filter "author" should be set to operator "" and value "All"

  Scenario: Successfully filter proposals and partial approve them
    Given I partially approve:
      | product   | author | attribute | locale | scope |
      | my-jacket | Mary   | name      | en_US  |       |
      | my-jacket | Mary   | price     |        |       |
    Then the filter "author" should be set to operator "" and value "All"
