Feature: Set the attribute used as label
  In order to let the user which attribute is the most accurate as the product title
  As a user
  I need to be able to set the attribute used as the label

  Background:
    Given the following family:
      | code |
      | Bags |
    And the following product attributes:
      | label       | family | type     |
      | Brand       | Bags   | text     |
      | Model       | Bags   | text     |
      | Size        | Bags   | number   |
      | Description | Bags   | textarea |

  Scenario: Fail to set a non-text attribute as the family label
    Given I am logged in as "admin"
    When I am on the "Bags" family page
    Then eligible attributes as label should be Id, Brand and Model

  Scenario: Succesfully set a family attribute as the family label
    Given I am logged in as "admin"
    When I am on the "Bags" family page
    And I choose "Brand" as the label of the family
    Then I should see "Product family successfully updated."

  Scenario: Succesfully display the chosen attribute as the title of the product
    Given the attribute "Brand" has been chosen as the family "Bags" label
    And the following products:
      | sku      | family |
      | bag-jean | Bags   |
    And the following product value:
      | product  | attribute | value |
      | bag-jean | Brand     | Levis |
    And I am logged in as "admin"
    When I am on the "bag-jean" product page
    Then the title of the product should be "Products/en Levis"

  Scenario: Succesfully display the id as the title of the product
    Given the following products:
      | sku      |
      | bag-jean |
    And I am logged in as "admin"
    When I am on the "bag-jean" product page
    Then the title of the product should match "#^Products/en \d+$#"

  @javascript
  Scenario: Fail to remove an attribute that is used as the family label
    Given the attribute "Brand" has been chosen as the family "Bags" label
    And I am logged in as "admin"
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I remove the "Brand" attribute
    Then I should see "You cannot remove this attribute because it's used as label for the family."
