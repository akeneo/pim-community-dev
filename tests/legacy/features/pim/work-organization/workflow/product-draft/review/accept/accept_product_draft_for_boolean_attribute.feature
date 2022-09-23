@javascript @proposal-feature-enabled @permission-feature-enabled
Feature: Review a product draft
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                   | jackets           |
      | categories               | winter_top        |
      | sku                      | my-jacket         |
      | name-en_US               | Jacket            |
      | description-en_US-mobile | An awesome jacket |
      | number_in_stock-mobile   | 4                 |
      | number_in_stock-tablet   | 20                |
      | price                    | 45 USD            |
      | manufacturer             | Volcom            |
      | weather_conditions       | dry, wet          |
      | handmade                 | 0                 |
      | release_date-mobile      | 2014-05-14        |
      | length                   | 60 CENTIMETER     |
      | legacy_attribute         | legacy            |
      | datasheet                |                   |
      | side_view                |                   |

  # @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a boolean attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field    | value      |
      | Handmade | state(yes) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" column tab
    And I click on the "Approve all" action of the row which contains "Handmade"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" column tab
    Then the product Handmade should be "on"
