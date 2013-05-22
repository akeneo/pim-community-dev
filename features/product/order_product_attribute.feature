Feature: Order product attributes
  In order to always have the same attribute fields order in each locale
  As a user
  I need to be able to set an order parameter which will manage fields order

  Scenario: Successfully update an attribute position
    Given the "Car" product
    And the following attribute group:
      | name    |
      | General |
    And the following product attribute:
      | product | label         | group   |
      | Car     | Release Date | General |
    And the current language is english
    And I am logged in as "admin"
    When I am on the "Release date" attribute page
    And I change the attribute position to 40
    Then I should see "Attribute successfully saved"

  Scenario: Display a produt attribute fields ordered by their position
    Given the "Car" product
    And the following attribute groups:
      | name    |
      | General |
      | Shape   |
    And the following product attributes:
      | product | label        | position | group   |
      | Car     | Release date | 20       | General |
      | Car     | Manufacturer | 30       | General |
      | Car     | File upload  | 10       | General |
      | Car     | Color        | 10       | Shape   |
      | Car     | Weight       | 30       | Shape   |
      | Car     | Height       | 20       | Shape   |
    And the current language is english
    And I am logged in as "admin"
    When I am on the "Car" product page
    Then attributes in group "General" should be File upload, Release date and Manufacturer
    And attributes in group "Shape" should be Color, Height and Weight
