@javascript
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
  Scenario: Successfully propose an identifier attribute change
    When I change the SKU to "your-jacket"
    And I save the product
    Then attribute SKU of "my-jacket" should be "my-jacket"
    But the field SKU should contain "your-jacket"
    And I should see that SKU is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a text attribute change
    When I change the Name to "Coat"
    And I save the product
    Then the english localizable value name of "my-jacket" should be "Jacket"
    But the field Name should contain "Coat"
    And I should see that Name is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a textarea attribute change
    When I change the Description for scope mobile to "An awesome coat"
    And I save the product
    Then the english mobile description of "my-jacket" should be "An awesome jacket"
    But the product Description for scope "mobile" should be "An awesome coat"
    But I should see that Description is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a number attribute change
    When I visit the "Marketing" group
    And I change the Number in stock for scope mobile to "40"
    And I save the product
    Then the mobile scopable value number_in_stock of "my-jacket" should be "4"
    And the product Number in stock for locale "en_US" and scope "mobile" should be "40"
    And I should see that Number in stock is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a prices collection attribute change
    When I visit the "Marketing" group
    And I change the Price to "90 USD"
    And I change the Price to "150 EUR"
    And I save the product
    Then the prices "price" of product my-jacket should be:
      | currency | amount |
      | USD      | 45.00  |
      | EUR      |        |
    But the product Price in USD should be "90"
    But the product Price in EUR should be "150"
    And I should see that Price is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a simple select attribute change
    When I change the Manufacturer to "Nike"
    And I save the product
    Then the option "manufacturer" of product my-jacket should be "Volcom"
    But the product Manufacturer should be "Nike"
    And I should see that Manufacturer is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a multi select attribute change
    When I change the "Weather conditions" to "Dry, Wet, Hot, Cold"
    And I save the product
    Then the options "weather_conditions" of product my-jacket should be:
      | value |
      | dry   |
      | wet   |
    But the product Weather conditions should be "cold, dry, hot, wet"
    And I should see that Weather conditions is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a file attribute change
    When I visit the "Media" group
    And I attach file "akeneo.txt" to "Datasheet"
    And I save the product
    Then the file "Datasheet" of product my-jacket should be ""
    But the product Datasheet should be "akeneo.txt"
    And I should see that Datasheet is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose an image attribute change
    When I visit the "Media" group
    And I attach file "akeneo.jpg" to "Side view"
    And I save the product
    Then the file "side_view" of product my-jacket should be ""
    But the product Side view should be "akeneo.jpg"
    And I should see that Side view is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a boolean attribute change
    Given I should see the text "Handmade"
    And I check the "Handmade" switch
    And I save the product
    Then attribute handmade of "my-jacket" should be "false"
    But the product Handmade should be "on"
    And I should see that Handmade is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully accept a date attribute modification on a product draft
    When I change the Release date for scope mobile to "05/20/2014"
    And I save the product
    Then the mobile scopable value release_date of "my-jacket" should be "2014-05-14"
    But the product Release date for scope "mobile" should be "05/20/2014"
    And I should see that Release date is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully accept a metric attribute modification on a product draft
    When I visit the "Sizes" group
    And I change the Length to "40 Centimeter"
    And I save the product
    Then the metric "length" of product my-jacket should be "60"
    But the product Length should be "40 Centimeter"
    And I should see that Length is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a localized attribute change
    Given I switch the locale to "fr_FR"
    When I change the Nom to "Tricot"
    And I save the product
    Then the french localizable value name of "my-jacket" should be "Veste"
    But the product Nom for locale "fr_FR" should be "Tricot"
    And I should see that Nom is a modified value

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully propose a localized and scoped attribute change
    Given I switch the locale to "fr_FR"
    When I change the Description for scope mobile to "Un beau tricot"
    And I save the product
    Then the french mobile description of "my-jacket" should be "Une superbe veste"
    But the product Description for scope "mobile" should be "Un beau tricot"
    And I should see that Description is a modified value
