@javascript
Feature: Submit a product changes proposition
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values

  # TODO Change admin when contributor and owner roles have been introduce
  Scenario: Successfully propose an identifier attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "SKU" to "your-sandals"
    And I save the product
    Then attribute SKU of "my-sandals" should be "my-sandals"

  Scenario: Successfully propose a text attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Name" to "Tong"
    And I save the product
    Then the english name of "my-sandals" should be "Sandals"

  Scenario: Successfully propose a textarea attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | description-en_US-mobile |
      | my-sandals | sandals | Some awesome sandals     |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "mobile Description" to "Some awesome baskets"
    And I save the product
    Then the english mobile description of "my-sandals" should be "Some awesome sandals"

  Scenario: Successfully propose a number attribute change
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Internal" group
    And I expand the "Number in stock" attribute
    And I change the "ecommerce Number in stock" to "4"
    And I change the "print Number in stock" to "8"
    And I change the "tablet Number in stock" to "15"
    And I save the product
    Then the english ecommerce number_in_stock of "my-tshirt" should be "2"
    Then the english print number_in_stock of "my-tshirt" should be "5"
    Then the english tablet number_in_stock of "my-tshirt" should be "20"

  Scenario: Successfully propose a prices collection attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | price-USD |
      | my-sandals | sandals | 45        |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I visit the "Marketing" group
    And I change the "$ Price" to "90"
    And I change the "€ Price" to "150"
    And I save the product
    Then the prices "price" of product my-sandals should be:
      | currency | amount |
      | USD      | 45.00  |
      | EUR      |        |

  Scenario: Successfully propose a simple select attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | manufacturer |
      | my-sandals | sandals | Converse     |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Manufacturer" to "TimberLand"
    And I save the product
    Then the option "manufacturer" of product my-sandals should be "Converse"

  Scenario: Successfully propose a multi select attribute change
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | weather_conditions |
      | my-sandals | sandals | dry, wet           |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Weather conditions" to "Hot, Cold"
    And I save the product
    Then the options "weather_conditions" of product my-sandals should be:
      | value |
      | dry   |
      | wet   |

  Scenario: Successfully propose a file attribute change
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Internal" group
    And I expand the "Number in stock" attribute
    And I change the "ecommerce Number in stock" to "1"
    And I change the "print Number in stock" to "1"
    And I change the "tablet Number in stock" to "1"
    And I attach file "akeneo.txt" to "Datasheet"
    And I save the product
    Then the file "Datasheet" of product my-tshirt should be ""

  Scenario: Successfully propose an image attribute change
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Media" group
    And I attach file "akeneo.jpg" to "Thumbnail"
    And I save the product
    Then the file "Thumbnail" of product my-tshirt should be ""

  Scenario: Successfully propose a boolean attribute change
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet | handmade |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     | no       |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Additional information" group
    And I check the "Handmade" switch
    And I save the product
    Then attribute handmade of "my-tshirt" should be "false"

  # Fix date diff
  @skip
  Scenario: Successfully accept a date attribute product changes proposition
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | release_date-ecommerce | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2014-05-14             | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Sales" group
    And I change the "ecommerce Release date" to "2014-05-20"
    And I save the product
    Then the english ecommerce release_date of "my-tshirt" should be "2014-05-14"

  Scenario: Successfully accept a metric attribute product changes proposition
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | washing_temperature | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 60 CELSIUS          | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Additional information" group
    And I change the "Washing temperature" to "40"
    And I save the product
    Then the metric "washing_temperature" of product my-tshirt should be "60"
