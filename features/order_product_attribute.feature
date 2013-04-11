Feature: Order product attributes
  In order to always have the same attribute fields order in each locale
  As a user
  I need to be able to set an order paramater which will manage fields order

  Scenario: Successfully update an attribute position
    Given the following attribute group:
      | name    |
      | General |
    And the following product attribute:
      | code         | group   |
      | releaseDate  | General |
    And a "Car" product
    And the current language is english
    And I am logged in as "Admin"
    When I am on the "releaseDate" attribute page
    And I change the attribute position to 40
    Then I should see "Attribute successfully saved"

  Scenario: Display a produt attribute fields ordered by their position
    Given the following attribute groups:
      | name    |
      | General |
      | Shape   |
    And the following product attributes:
      | code         | position | group   |
      | releaseDate  | 20       | General |
      | manufacturer | 30       | General |
      | fileUpload   | 10       | General |
      | color        | 10       | Shape   |
      | weight       | 30       | Shape   |
      | height       | 20       | Shape   |
    And a "Car" product
    And the current language is english
    And I am logged in as "Admin"
    When I am on the "Car" product page
    Then attributes in group "General" should be fileUpload, manufacturer and releaseDate
    And attributes in group "Shape" should be color, height and weight
