@javascript
Feature: Add products to a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to add products to a variant group

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku             | family  | categories        | size | color | name-en_US |
      | sandal-white-37 | sandals | winter_collection | 37   | white | old name   |
      | sandal-white-38 | sandals | winter_collection | 38   | white | old name   |
      | sandal-white-39 | sandals | winter_collection | 39   | white | old name   |
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | size, color | VARIANT |
    And the following variant group values:
      | group  | attribute    | value       | locale | scope |
      | SANDAL | manufacturer | Converse    |        |       |
      | SANDAL | name         | EN name     | en_US  |       |
      | SANDAL | comment      | New comment |        |       |
    And I am logged in as "Julia"

  Scenario: Successfully add products in variant groups, history should be updated with a variant group context
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-white-37
    And I check the row "sandal-white-37"
    And I press the "Save" button
    Then the row "sandal-white-37" should be checked
    When I edit the "sandal-white-37" product
    And I open the history
    Then I should see history:
      | version | author                                                            | property | value           |
      | 2       | Julia Stark - Julia@example.com (Comes from variant group SANDAL) | groups   | SANDAL          |
      | 1       | Admin Doe - admin@example.com                                     | SKU      | sandal-white-37 |
