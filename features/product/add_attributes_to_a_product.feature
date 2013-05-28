Feature: Add attributes to a product
  In order to provide more information about a product
  As an user
  I need to be able to add attributes to a product

  Background:
    Given the "Car" product
    And the following attribute group:
      | name    |
      | General |
      | Prices  |
    And the following product attribute:
      | product | label        | group   |
      | Car     | Release Date | General |
    And the following attributes:
      | label            | group   |
      | Long Description | General |
      | Manufacturer     | General |
      | Size             |         |
    And I am logged in as "admin"

  Scenario: Display attributes that are not related to the current product
    Given I am on the "Car" product page
    Then I should see available attributes Long Description and Manufacturer in group "General"
    And I should see available attribute Size in group "Other"
    But I should not see available attribute Release Date in group "General"

  Scenario: Successfully add attributes to a product
    Given I am on the "Car" product page
    And I add available attributes Long Description and Size
    Then attributes in group "General" should be Release date and Long description
    And attribute in group "Other" should be Size
