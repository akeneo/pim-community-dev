@javascript
Feature: Review a product changes proposition
  In order to control which data should be applied to a product
  As an owner
  I need to be able to review product changes proposition

  # TODO Change admin when contributor and owner roles have been introduce
  Scenario: Successfully accept an identifier attribute product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "SKU" to "your-sandals"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "SKU"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product SKU should be "your-sandals"

  Scenario: Successfully accept a text attribute product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Name" to "Tong"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Name"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Tong"

  Scenario: Successfully accept a textarea attribute product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | description-en_US-mobile |
      | my-sandals | sandals | Some awesome sandals     |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "mobile Description" to "Some awesome baskets"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "mobile - Description"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product mobile Description should be "Some awesome baskets"

  Scenario: Successfully accept a number attribute product changes proposition
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
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Number in stock"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product ecommerce Number in stock should be "4"
    Then the product print Number in stock should be "8"
    Then the product tablet Number in stock should be "15"

  Scenario: Successfully accept a prices attribute product changes proposition
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
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Price"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Price in $ should be "90.00"
    Then the product Price in € should be "150.00"

  Scenario: Successfully accept a simpleselect attribute product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | manufacturer |
      | my-sandals | sandals | Converse     |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Manufacturer" to "TimberLand"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Manufacturer"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Manufacturer should be "TimberLand"

  Scenario: Successfully accept a multiselect attribute product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | weather_conditions |
      | my-sandals | sandals | dry, wet           |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Weather conditions" to "Hot, Cold"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Weather conditions"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Weather conditions should be "Cold, Dry, Hot and Wet"

  Scenario: Successfully accept a file attribute product changes proposition
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
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Datasheet"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then I should see "akeneo.txt"

  Scenario: Successfully accept an image attribute product changes proposition
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Media" group
    And I attach file "akeneo.jpg" to "Thumbnail"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Thumbnail"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then I should see "akeneo.jpg"

  Scenario: Successfully accept a boolean attribute product changes proposition
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce | number_in_stock-print | number_in_stock-tablet |
      | my-tshirt | tshirts | 2                         | 5                     | 20                     |
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I visit the "Additional information" group
    And I check the "Handmade" switch
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Handmade"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Handmade should be "1"

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
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "ecommerce - Release date"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product ecommerce Release date should be "2014-05-20"

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
    When I visit the "Propositions" tab
    And I click on the "approve" action of the row which contains "Washing temperature"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Washing temperature should be "40"

  Scenario: Successfully refuse a product changes proposition
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Name" to "Tong"
    And I save the product
    When I visit the "Propositions" tab
    And I click on the "refuse" action of the row which contains "Name"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Sandals"
