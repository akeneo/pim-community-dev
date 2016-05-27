@javascript
Feature: Display proposals in datagrid
  In order to summarize proposals
  As a product manager
  I need to be able to view a proposal

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | jackets       |
      | categories | winter_top    |
      | sku        | my-jacket     |
      | price      | 45 USD,75 EUR |

  Scenario: Successfully propose to remove a price attribute
    Given Mary proposed the following change to "my-jacket":
      | field | value | tab       |
      | Price | EUR   | Marketing |
      | Price | 5 USD | Marketing |
    When I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I visit the "Proposals" tab
    Then I should see the following proposals:
      | product   | author | attribute | original       | new   |
      | my-jacket | Mary   | price     | €75.00, $45.00 | $5.00 |

  Scenario: Successfully display only updated price attribute
    Given  Mary proposed the following change to "my-jacket":
      | field | value  | tab       |
      | Price | 5 USD  | Marketing |
    When I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I visit the "Proposals" tab
    Then I should see the following proposals:
      | product   | author | attribute | original | new   |
      | my-jacket | Mary   | price     | $45.00   | $5.00 |

  Scenario: Successfully display only new price attribute
    Given the following product values:
      | product   | attribute | value |
      | my-jacket | price     |       |
    And Mary proposed the following change to "my-jacket":
      | field | value  | tab       |
      | Price | 5 USD  | Marketing |
    When I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I visit the "Proposals" tab
    Then I should see the following proposals:
      | product   | author | attribute | original | new   |
      | my-jacket | Mary   | price     |          | $5.00 |
