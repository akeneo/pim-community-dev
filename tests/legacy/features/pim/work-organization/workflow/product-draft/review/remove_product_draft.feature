@javascript
Feature: Review a product draft
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the "jackets" family page
    And I visit the "Attributes" tab
    And I add available attribute Old attribute not used anymore
    And I save the family
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
    And I logout

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully remove an in progress product draft
    Given Mary started to propose the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" column tab
    And I click on the "Remove" action of the row which contains "Jacket"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" column tab
    Then the product Name should be "Jacket"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Not being able to delete a draft with values I can't edit
    Given Mary started to propose the following change to "my-jacket":
      | field                          | value                                | tab                        |
      | Old attribute not used anymore | a new value for the legacy attribute | old group not used anymore |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" column tab
    Then I should not be able to view the "Delete" action of the row which contains "Mary"
    And I should see the text "Can't be deleted"
