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
  Scenario: Successfully accept a file attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Datasheet | file(akeneo.txt) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Datasheet"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.txt"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept an image attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Side view | file(akeneo.jpg) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Side view"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.jpg"

