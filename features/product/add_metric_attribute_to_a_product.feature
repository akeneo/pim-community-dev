@javascript
Feature: Add metric attribute to a product
  In order to provide more information about a product
  As a user
  I need to be able to add a metric attribute to a product

  Background:
    Given a "Car" product available in english
    And the following product attribute:
      | type   | label  | metric family | default metric unit | position |
      | metric | Weight | Weight        | KILOGRAM            | 2        |
    And I am logged in as "admin"

  Scenario: Successfully add a metric attribute to a product
    Given I am on the "Car" product page
    And I add available attribute Weight
    Then attributes in group "Other" should be SKU and Weight
