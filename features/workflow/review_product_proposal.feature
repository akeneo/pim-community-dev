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
    And "admin" has submitted the following proposal for "my-sandals":
      | sku | your-sandals |
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "your-sandals"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product SKU should be "your-sandals"

  Scenario: Succesfully accept a text attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And role "Administrator" has the right to edit the attribute group "info"
    And "admin" has submitted the following proposal for "my-sandals":
      | name-en_US | Tong |
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Tong"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Tong"

  Scenario: Succesfully accept a textarea attribute product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | description-en_US-mobile |
      | my-sandals | sandals | Some awesome sandals     |
    And role "Administrator" has the right to edit the attribute group "info"
    And "admin" has submitted the following proposal for "my-sandals":
      | description-en_US-mobile | Some awesome baskets |
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "awesome"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product mobile Description should be "Some awesome baskets"

  Scenario: Succesfully accept a number attribute product changes proposal
    Given an "apparel" catalog configuration
    And the following product:
      | sku       | family  | number_in_stock-ecommerce |
      | my-tshirt | tshirts | 12                         |
    And role "Administrator" has the right to edit the attribute group "internal"
    And "admin" has submitted the following proposal for "my-tshirt":
      | number_in_stock-ecommerce | 7 |
    And I am logged in as "admin"
    When I edit the "my-tshirt" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "number_in_stock"
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
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Price in $ should be "90.00"
    Then the product Price in € should be "150.00"


  Scenario: Succesfully refuse a product changes proposal
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And role "Administrator" has the right to edit the attribute group "info"
    And "admin" has submitted the following proposal for "my-sandals":
      | name-en_US | Tong |
    And I am logged in as "admin"
    When I edit the "my-sandals" product
    When I visit the "Proposals" tab
    And I click on the "refuse" action of the row which contains "Tong"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Sandals"
