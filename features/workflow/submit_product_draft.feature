@javascript
Feature: Submit a modification on a product draft
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                    | jackets           |
      | categories                | winter_top        |
      | sku                       | my-jacket         |
      | name-en_US                | Jacket            |
      | name-fr_FR                | Veste             |
      | description-en_US-mobile  | An awesome jacket |
      | description-fr_FR-mobile  | Une superbe veste |
      | number_in_stock-ecommerce | 2                 |
      | number_in_stock-mobile    | 4                 |
      | number_in_stock-print     | 5                 |
      | number_in_stock-tablet    | 20                |
      | price-USD                 | 45                |
      | manufacturer              | Volcom            |
      | weather_conditions        | dry, wet          |
      | handmade                  | no                |
      | release_date-ecommerce    | 2014-05-14        |
      | length                    | 60 CENTIMETER     |
    And I am logged in as "Mary"
    And I edit the "my-jacket" product

  Scenario: Successfully propose an identifier attribute change
    When I change the "SKU" to "your-jacket"
    And I save the product
    Then attribute SKU of "my-jacket" should be "my-jacket"
    But the field SKU should contain "your-jacket"
    And I should see that SKU is a modified value

  Scenario: Successfully propose a text attribute change
    When I change the "Name" to "Coat"
    And I save the product
    Then the english name of "my-jacket" should be "Jacket"
    But the field Name should contain "Coat"
    And I should see that Name is a modified value

  Scenario: Successfully propose a textarea attribute change
    When I change the "mobile Description" to "An awesome coat"
    And I save the product
    Then the english mobile description of "my-jacket" should be "An awesome jacket"
    But the field mobile Description should contain "An awesome coat"
    But I should see that mobile Description is a modified value

  Scenario: Successfully propose a number attribute change
    When I visit the "Marketing" group
    And I expand the "Number in stock" attribute
    And I change the "ecommerce Number in stock" to "20"
    And I change the "mobile Number in stock" to "40"
    And I change the "print Number in stock" to "50"
    And I save the product
    And I expand the "Number in stock" attribute
    Then the english ecommerce number_in_stock of "my-jacket" should be "2"
    And the field ecommerce Number in stock should contain "20"
    And I should see that ecommerce Number in stock is a modified value
    And the english mobile number_in_stock of "my-jacket" should be "4"
    And the field mobile Number in stock should contain "40"
    And I should see that mobile Number in stock is a modified value
    And the english print number_in_stock of "my-jacket" should be "5"
    And the field print Number in stock should contain "50"
    And I should see that print Number in stock is a modified value

  Scenario: Successfully propose a prices collection attribute change
    When I visit the "Marketing" group
    And I change the "$ Price" to "90"
    And I change the "€ Price" to "150"
    And I save the product
    Then the prices "price" of product my-jacket should be:
      | currency | amount |
      | USD      | 45.00  |
      | EUR      |        |
    But the field Price in $ should contain "90"
    And I should see that Price in $ is a modified value
    And the field Price in € should contain "150"
    And I should see that Price in € is a modified value

  Scenario: Successfully propose a simple select attribute change
    When I change the "Manufacturer" to "Nike"
    And I save the product
    Then the option "manufacturer" of product my-jacket should be "Volcom"
    But the field Manufacturer should contain "Nike"
    And I should see that Manufacturer is a modified value

  Scenario: Successfully propose a multi select attribute change
    When I change the "Weather conditions" to "Dry, Wet, Hot, Cold"
    And I save the product
    Then the options "weather_conditions" of product my-jacket should be:
      | value |
      | dry   |
      | wet   |
    But the field Weather conditions should contain "Dry, Wet, Hot and Cold"
    And I should see that Weather conditions is a modified value

  Scenario: Successfully propose a file attribute change
    When I visit the "Media" group
    And I attach file "akeneo.txt" to "Datasheet"
    And I save the product
    Then the file "Datasheet" of product my-jacket should be ""
    But the field Datasheet should contain "akeneo.txt"
    And I should see that Datasheet is a modified value

  Scenario: Successfully propose an image attribute change
    When I visit the "Media" group
    And I attach file "akeneo.jpg" to "Side view"
    And I save the product
    Then the file "side_view" of product my-jacket should be ""
    But the field Side view should contain "akeneo.jpg"
    And I should see that Side view is a modified value

  Scenario: Successfully propose a boolean attribute change
    When I check the "Handmade" switch
    And I save the product
    Then attribute handmade of "my-jacket" should be "false"
    But the "Handmade" checkbox should be checked
    And I should see that Handmade is a modified value

  @skip
  Scenario: Successfully accept a date attribute modification on a product draft
    When I change the "ecommerce Release date" to "2014-05-20"
    And I save the product
    Then the english ecommerce release_date of "my-jacket" should be "2014-05-14"
    But the field ecommerce Release date should contain "May 20, 2014"
    And I should see that ecommerce Release date is a modified value

  Scenario: Successfully accept a metric attribute modification on a product draft
    When I visit the "Sizes" group
    And I change the "Length" to "40"
    And I save the product
    Then the metric "length" of product my-jacket should be "60"
    But the field Length should contain "40"
    And I should see that Length is a modified value

  Scenario: Successfully propose a localized attribute change
    Given I switch the locale to "French (France)"
    When I change the "Nom" to "Tricot"
    And I save the product
    Then the french name of "my-jacket" should be "Veste"
    But the field Nom should contain "Tricot"
    And I should see that Nom is a modified value

  Scenario: Successfully propose a localized and scoped attribute change
    Given I switch the locale to "French (France)"
    When I change the "mobile Description" to "Un beau tricot"
    And I save the product
    Then the french mobile description of "my-jacket" should be "Une superbe veste"
    But the field mobile Description should contain "Un beau tricot"
    And I should see that mobile Description is a modified value
