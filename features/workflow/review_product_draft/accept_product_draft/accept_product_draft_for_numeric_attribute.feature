@javascript
Feature: Review a product draft
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                    | jackets           |
      | categories                | winter_top        |
      | sku                       | my-jacket         |
      | name-en_US                | Jacket            |
      | description-en_US-mobile  | An awesome jacket |
      | number_in_stock-mobile    | 4                 |
      | number_in_stock-tablet    | 20                |
      | price                     | 45 USD            |
      | manufacturer              | Volcom            |
      | weather_conditions        | dry, wet          |
      | handmade                  | 0                 |
      | release_date-mobile       | 2014-05-14        |
      | length                    | 60 CENTIMETER     |
      | legacy_attribute          | legacy            |
      | datasheet                 |                   |
      | side_view                 |                   |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a number attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab       | scope  | field           | value |
      | Marketing | mobile | Number in stock | 40    |
      | Marketing | tablet | Number in stock | 200   |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "Approve all" action of the row which contains "Number in stock"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Number in stock for scope "mobile" should be "40"
    Then the product Number in stock for scope "tablet" should be "200"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a prices attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab       | field | value   |
      | Marketing | Price | 90 USD  |
      | Marketing | Price | 150 EUR |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "Approve all" action of the row which contains "Price"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Price in USD should be "90.00"
    Then the product Price in EUR should be "150.00"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a date attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab                 | field        | value      |
      | Product information | Release date | 05/20/2014 |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "Approve all" action of the row which contains "Release date"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Release date for scope "mobile" should be "05/20/2014"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a metric attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field   | value         |
      | Sizes | Length  | 40 Centimeter |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "Approve all" action of the row which contains "Mary"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Sizes" group
    Then the product Length should be "40 Centimeter"
