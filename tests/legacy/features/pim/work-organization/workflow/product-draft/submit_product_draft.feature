@javascript @proposal-feature-enabled
Feature: Submit a modification on a product draft
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                   | jackets           |
      | categories               | winter_top        |
      | sku                      | my-jacket         |
      | name-en_US               | Jacket            |
      | name-fr_FR               | Veste             |
      | description-en_US-mobile | An awesome jacket |
      | description-fr_FR-mobile | Une superbe veste |
      | number_in_stock-mobile   | 4                 |
      | number_in_stock-tablet   | 20                |
      | price                    | 45 USD            |
      | manufacturer             | Volcom            |
      | weather_conditions       | dry, wet          |
      | handmade                 | 0                 |
      | release_date-mobile      | 2014-05-14        |
      | length                   | 60 CENTIMETER     |
      | datasheet                |                   |
      | side_view                |                   |
    And I am logged in as "Mary"
    And I edit the "my-jacket" product

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a localized and scoped attribute change
    Given I switch the locale to "fr_FR"
    When I change the Description for scope mobile to "Un beau tricot"
    And I save the product
    Then the french mobile description of "my-jacket" should be "Une superbe veste"
    But the product Description for scope "mobile" should be "Un beau tricot"
    And I should see that Description is a modified value
