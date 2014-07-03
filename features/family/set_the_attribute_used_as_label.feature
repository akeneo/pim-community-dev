@javascript
Feature: Set the attribute used as label
  In order to let the user which attribute is the most accurate as the product title
  As an administrator
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
    And I am logged in as "Peter"

  Scenario: Succesfully set a family attribute as the family label
    Given I am on the "Bags" family page
    Then eligible attributes as label should be SKU, Brand and Model

  Scenario: Succesfully set a family attribute as the family label
    Given I am on the "Bags" family page
    And I fill in the following information:
      | Attribute used as label | Brand |
    And I save the family
    Then I am on the families page
    And I should see "Brand"

  Scenario: Succesfully display the chosen attribute as the title of the product
    Given the attribute "Brand" has been chosen as the family "Bags" label
    And the following products:
      | sku      | family | brand |
      | bag-jean | Bags   | Levis |
    When I am on the "bag-jean" product page
    Then the title of the product should be "Product/en Levis"

  Scenario: Succesfully display the id as the title of the product
    Given the following products:
      | sku      |
      | bag-jean |
    When I am on the "bag-jean" product page
    Then the title of the product should be "Product/en bag-jean"

  Scenario: Fail to remove an attribute that is used as the family label
    Given the attribute "Brand" has been chosen as the family "Bags" label
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I remove the "Brand" attribute
    Then I should see flash message "This attribute can not be removed because it is used as the label of the family"
