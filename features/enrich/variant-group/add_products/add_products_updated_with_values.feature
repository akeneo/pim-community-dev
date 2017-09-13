@javascript @skip
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
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | SANDAL | Sandal      | size,color | VARIANT |
    And the following variant group values:
      | group  | attribute    | value       | locale | scope |
      | SANDAL | manufacturer | Converse    |        |       |
      | SANDAL | name         | EN name     | en_US  |       |
      | SANDAL | comment      | New comment |        |       |
    And I am logged in as "Julia"

  Scenario: Successfully add products in variant groups, products are updated with variant group values
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-white-37, sandal-white-38, sandal-white-39
    And I check the row "sandal-white-37"
    And I check the row "sandal-white-38"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    And the rows "sandal-white-37 and sandal-white-38" should be checked
    And the product "sandal-white-37" should have the following value:
      | name-en_US   | EN name     |
      | comment      | New comment |
      | manufacturer | [Converse]  |
    And the product "sandal-white-38" should have the following value:
      | name-en_US   | EN name     |
      | comment      | New comment |
      | manufacturer | [Converse]  |
    And the product "sandal-white-39" should have the following value:
      | name-en_US | old name |
