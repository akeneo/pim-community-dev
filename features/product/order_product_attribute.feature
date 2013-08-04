Feature: Order product attributes
  In order to always have the same attribute fields order in each locale
  As a user
  I need to be able to set an order parameter which will manage fields order

  @javascript
  Scenario: Successfully update an attribute position
    Given a "Car" product available in english
    And the following attribute group:
      | name    |
      | General |
    And the following product attribute:
      | product | label        | group   | position |
      | Car     | Release Date | General | 1        |
      | Car     | Color        | General | 2        |
      | Car     | Price        | General | 3        |
    And I am logged in as "admin"
    When I am on the "General" group page
    And I visit the "Attributes" tab
    Then the attribute "Price" should be in position 3
    And I change the attribute "Price" position to 1
    Then I should see "Group successfully saved"
    And I visit the "Attributes" tab
    Then the attribute "Price" should be in position 1
    And the attribute "Color" should be in position 3

  Scenario: Display a produt attribute fields ordered by their position
    Given a "Car" product available in english
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
    And I am logged in as "admin"
    When I am on the "Car" product page
    Then attributes in group "General" should be File upload, Release date and Manufacturer
    And attributes in group "Shape" should be Color, Height and Weight
