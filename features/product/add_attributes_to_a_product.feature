@javascript
Feature: Add attributes to a product
  In order to provide more information about a product
  As an user
  I need to be able to add attributes to a product

  Background:
    Given a "Car" product available in english
    And the following attribute group:
      | name    |
      | General |
      | Prices  |
    And the following product attribute:
      | product | label        | group   | position |
      | Car     | Release Date | General | 1        |
    And the following product attributes:
      | label            | group   | position |
      | Long Description | General | 10       |
      | Manufacturer     | General | 20       |
      | Size             |         | 10       |
    And I am logged in as "admin"

  Scenario: Display attributes that are not related to the current product
    Given I am on the "Car" product page
    Then I should see available attributes Long Description and Manufacturer in group "General"
    And I should see available attribute Size in group "Other"
    But I should not see available attribute Release Date in group "General"

  Scenario: Successfully add attributes to a product
    Given I am on the "Car" product page
    And I add available attributes Long Description and Size
    Then attributes in group "General" should be Release Date and Long Description
    And attribute in group "Other" should be SKU and Size
