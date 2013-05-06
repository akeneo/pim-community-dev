Feature: Add attributes to a product
  In order to provide more information about a product
  As an user
  I need to be able to add attributes to a product

  Scenario: Display attributes that are not related to the current product
    Given the "Car" product
    And the following attribute group:
      | name    |
      | General |
      | Prices  |
    And the following product attribute:
      | product | name         | group   |
      | Car     | Release Date | General |
    And the following attributes:
      | name             | group   |
      | Long Description | General |
      | Manufacturer     | General |
      | Size             | General |
      | Cost             | Prices  |
    And the current language is english
    And I am logged in as "admin"
    When I am on the "Car" product page
    And I click on "Add Attribute"
    Then I should see attributes Long Description, Manufacturer and Size in group "General"
    And I should see attribute Cost in group "Prices"
    But I should not see attribute Release Date in group "General"
