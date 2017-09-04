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

  @jira https://akeneo.atlassian.net/browse/PIM-3736
  Scenario: Reject product addition in a variant group, products count should be correct
    Given the following products:
      | sku              | family  | categories        | size | color | name-en_US |
      | sandal-white-37  | sandals | winter_collection | 37   | white | old name   |
      | sandal-white-38  | sandals | winter_collection | 38   | white | old name   |
      | sandal-white-39  | sandals | winter_collection | 39   | white | old name   |
      | duplicate-sandal | sandals | winter_collection | 39   | white | old name   |
    And I am on the "SANDAL" variant group page
    Then the grid should contain 4 elements
    And I check the row "sandal-white-37"
    And I check the row "sandal-white-38"
    And I check the row "sandal-white-39"
    And I save the variant group
    Then I should see the text "Products: 3"
    And I check the row "duplicate-sandal"
    And I save the variant group
    Then I should see the text "Products: 3"
    And the row "duplicate-sandal" should be checked
