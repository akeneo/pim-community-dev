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
      | product | code        | group   |
      | Car     | releaseDate | General |
    And the current language is english
    And I am logged in as "admin"
    When I am on the "releaseDate" attribute page
    And I change the attribute position to 40
    Then I should see "Attribute successfully saved"

  Scenario: Display a produt attribute fields ordered by their position
    Given the "Car" product
    And the following attribute groups:
      | name    |
      | General |
      | Shape   |
    And the following product attributes:
      | product | code         | position | group   |
      | Car     | releaseDate  | 20       | General |
      | Car     | manufacturer | 30       | General |
      | Car     | fileUpload   | 10       | General |
      | Car     | color        | 10       | Shape   |
      | Car     | weight       | 30       | Shape   |
      | Car     | height       | 20       | Shape   |
    And the current language is english
    And I am logged in as "admin"
    When I am on the "Car" product page
    Then attributes in group "General" should be fileUpload, releaseDate and manufacturer
    And attributes in group "Shape" should be color, height and weight
