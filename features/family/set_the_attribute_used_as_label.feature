Feature: Set the attribute used as label
  In order to let the user which attribute is the most accurate as the product title
  As a user
  I need to be able to set the attribute used as the label

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code |
      | Bags |
    And the following attributes:
      | label       | families | type     |
      | Brand       | Bags     | text     |
      | Model       | Bags     | text     |
      | Size        | Bags     | number   |
      | Description | Bags     | textarea |
    And I am logged in as "admin"

  Scenario: Fail to set a non-text attribute as the family label
    Given I am on the "Bags" family page
    Then eligible attributes as label should be Id, Brand and Model

  Scenario: Succesfully set a family attribute as the family label
    Given I am on the "Bags" family page
    And I choose "Brand" as the label of the family
    Then I should see "Family successfully updated"

  Scenario: Succesfully display the chosen attribute as the title of the product
    Given the attribute "Brand" has been chosen as the family "Bags" label
    And the following products:
      | sku      | family |
      | bag-jean | Bags   |
    And the following product value:
      | product  | attribute | value |
      | bag-jean | Brand     | Levis |
    When I am on the "bag-jean" product page
    Then the title of the product should be "Product/en Levis"

  Scenario: Succesfully display the id as the title of the product
    Given the following products:
      | sku      |
      | bag-jean |
    When I am on the "bag-jean" product page
    Then the title of the product should be "Product/en bag-jean"

  @javascript
  Scenario: Fail to remove an attribute that is used as the family label
    Given the attribute "Brand" has been chosen as the family "Bags" label
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I remove the "Brand" attribute
    Then I should see flash message "This attribute can not be removed because it is used as the label of the family"
