@javascript
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
      | gallery                  | bridge            |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a multiselect attribute from a product draft
    Given I am logged in as "Mary"
    And I edit the "my-jacket" product
    And I visit the "Media" group
    And I start to manage assets for "gallery"
    When I check the row "paint"
    And I check the row "machine"
    Then the item picker basket should contain paint, machine
    When I confirm the asset modification
    Then the "gallery" asset gallery should contain paint, machine, bridge
    When I save the product
    And I press the "Send for approval" button
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" column tab
    Then I should see the text "Photo of a paint."
    And I should see the text "Architectural bridge of a city..."
    And I should see the text "A big machine"
