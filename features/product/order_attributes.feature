Feature: Order attributes
  In order to always have the same attribute fields order in each locale
  As a user
  I need to be able to set an order parameter which will manage fields order

  Background:
    Given the "footwear" catalog configuration
    And a "Rangers" product
    And I am logged in as "admin"

  @javascript @skip
  Scenario: Successfully update an attribute position
    Given I am on the "info" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "Length" should be in position 6
    And I change the attribute "Length" position to 1
    When I am on the "info" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "Length" should be in position 1
    And the attribute "Description" should be in position 3

  Scenario: Display attribute fields ordered by their position
    Given the following attributes:
      | label         | sort order | group |
      | Sole weight   | 20         | info  |
      | Provider      | 30         | info  |
      | File upload   | 10         | info  |
      | Sole color    | 10         | other |
      | Sole material | 30         | other |
      | Height        | 20         | other |
    And the "Rangers" product has the "soleWeight, Provider, fileUpload, soleColor, soleMaterial and height" attributes
    When I am on the "Rangers" product page
    Then attributes in group "info" should be SKU, File upload, Sole weight and Provider
    And attributes in group "Other" should be Sole color, Height and Sole material
