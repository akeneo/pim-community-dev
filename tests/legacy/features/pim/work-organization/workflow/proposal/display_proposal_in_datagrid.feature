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
      | name-en_US | Jackets       |

  @github https://github.com/akeneo/pim-community-dev/issues/10083
  Scenario: Successfully display removed data in the proposal datagrid
    Given Mary proposed the following change to "my-jacket":
      | field | value |
      | Name  |       |
    When I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I visit the "Proposals" column tab
    Then I should see the following proposals:
      | product | author | attribute | original | new |
      | Jackets | Mary   | name      | Jackets  |     |

  Scenario: Successfully display an updated price attribute
    Given Mary proposed the following change to "my-jacket":
      | field | value | tab       |
      | Price | 5 USD | Marketing |
    When I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I visit the "Proposals" column tab
    Then I should see the following proposals:
      | product | author | attribute | original | new   |
      | Jackets | Mary   | price     | $45.00   | $5.00 |
