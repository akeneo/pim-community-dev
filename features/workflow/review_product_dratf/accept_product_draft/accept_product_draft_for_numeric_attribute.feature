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
    Given the following product drafts:
      | product   | author | result                                                                                                                      | status |
      | my-jacket | Mary   | {"values":{"number_in_stock":[{"locale":null,"scope":"mobile","data":"40"},{"locale":null,"scope":"tablet","data":"200"}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Number in stock"
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
    And I click on the "approve" action of the row which contains "Price"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Price in USD should be "90.00"
    Then the product Price in EUR should be "150.00"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a date attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                             | status |
      | my-jacket | Mary   | {"values":{"release_date":[{"locale":null,"scope":"mobile","data":"2014-05-20"}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Release date"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Release date for scope "mobile" should be "2014-05-20"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a metric attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                                        | status |
      | my-jacket | Mary   | {"values":{"length":[{"locale":null,"scope":null,"data":{"data":"40","unit":"CENTIMETER"}}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Length"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Sizes" group
    Then the product Length should be "40 CENTIMETER"
