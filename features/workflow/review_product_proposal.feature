@javascript
Feature: Review a product changes proposal
  In order to control which data should be applied to a product
  As an owner
  I need to be able to review product changes proposal

  # TODO Change admin when contributor and owner roles have been introduced

  Scenario: Succesfully accept an identifier attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "SKU" to "your-sandals"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "your-sandals"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product SKU should be "your-sandals"

  Scenario: Succesfully accept a text attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Name" to "Tong"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Tong"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Tong"

  Scenario: Succesfully accept a textarea attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | description-en_US-mobile |
      | my-sandals | sandals | Some awesome sandals     |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "mobile Description" to "Some awesome baskets"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "awesome"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product mobile Description should be "Some awesome baskets"

  Scenario: Succesfully accept a number attribute product changes proposal
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce |
      | my-tshirt | tshirts | 12                         |
    And role "Administrator" has the right to edit the attribute group "internal"
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I change the "ecommerce Number in stock" to "7"
    And I save the product
    When I edit the "my-tshirt" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "number_in_stock"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product ecommerce Number in stock should be "7"

  Scenario: Succesfully accept a prices attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | price-USD |
      | my-sandals | sandals | 45        |
    And role "Administrator" has the right to edit the attribute group "marketing"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "$ Price" to "90"
    And I change the "€ Price" to "150"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "price"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Price in $ should be "90.00"
    Then the product Price in € should be "150.00"

  Scenario: Succesfully accept a simpleselect attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | manufacturer |
      | my-sandals | sandals | Converse     |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Manufacturer" to "TimberLand"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "manufacturer"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Manufacturer should be "TimberLand"

  Scenario: Succesfully accept a multiselect attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | weather_conditions |
      | my-sandals | sandals | dry, wet           |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Weather conditions" to "Hot, Cold"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "weather_conditions"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Weather conditions should be "Cold, Dry, Hot and Wet"

  Scenario: Succesfully accept a boolean attribute product changes proposal
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  |
      | my-tshirt | tshirts |
    And role "Administrator" has the right to edit the attribute group "additional"
    And I am logged in as "admin"
    And I edit the "my-tshirt" product
    And I check the "Handmade" switch
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "handmade"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Handmade should be "1"


  # This scenario is bugged because the date retrieval create DateTime with H-2
  @skip
  Scenario: Succesfully accept a date attribute product changes proposal
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | release_date-ecommerce |
      | my-tshirt | tshirts | 2014-05-14             |
    And role "Administrator" has the right to edit the attribute group "sales"
    And "admin" has submitted the following proposal for "my-tshirt":
      | release_date--ecommerce | 2014-05-20 |
    And I am logged in as "admin"
    When I edit the "my-tshirt" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "release_date"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product ecommerce Release date should be "2014-05-20"

  # Scenario: Succesfully accept a metric attribute product changes proposal

  Scenario: Succesfully refuse a product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And role "Administrator" has the right to edit the attribute group "info"
    And I am logged in as "admin"
    And I edit the "my-sandals" product
    And I change the "Name" to "Tong"
    And I save the product
    When I visit the "Proposals" tab
    And I click on the "refuse" action of the row which contains "Tong"
    And I filter by "Status" with value "Waiting"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Sandals"
