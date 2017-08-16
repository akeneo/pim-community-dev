@javascript
Feature: Add products to a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to add products to a variant group

  Background:
    Given the "footwear" catalog configuration
    And the following variant groups:
      | code   | label-en_US | axis  | type    |
      | SANDAL | Sandal      | color | VARIANT |
    And the following variant group values:
      | group  | attribute    | value       | locale | scope |
      | SANDAL | manufacturer | Converse    |        |       |
      | SANDAL | name         | EN name     | en_US  |       |
      | SANDAL | comment      | New comment |        |       |
    And I am logged in as "admin"
    And I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | sandal-white-37 |
      | family | Sandals         |
    And I press the "Save" button in the popin
    And I wait to be on the "sandal-white-37" product page
    And I visit the "Colors" group
    And I fill in the following information:
      | Color | White |
    And I save the product
    And I logout
    And I am logged in as "Julia"

  Scenario: Successfully add products in variant groups, history should be updated with a variant group context
    Given I am on the "SANDAL" variant group page
    And I should see products sandal-white-37
    And I check the row "sandal-white-37"
    And I save the variant group
    Then the row "sandal-white-37" should be checked
    When I edit the "sandal-white-37" product
    And I visit the "History" column tab
    Then I should see history:
      | version | author                                        | property | value           |
      | 3       | Julia Stark (Comes from variant group SANDAL) | groups   | SANDAL          |
      | 2       | John Doe                                      | Color    | white           |
      | 1       | John Doe                                      | SKU      | sandal-white-37 |
