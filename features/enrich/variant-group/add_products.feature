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

  Scenario: Successfully add products in variant groups, products are updated with variant group values
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-white-37, sandal-white-38, sandal-white-39
    And I check the row "sandal-white-37"
    And I check the row "sandal-white-38"
    And I press the "Save" button
    Then the rows "sandal-white-37 and sandal-white-38" should be checked
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

  Scenario: Successfully delete a variant group, product history should be updated without context
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-white-37
    And I check the row "sandal-white-37"
    And I press the "Save" button
    Then the row "sandal-white-37" should be checked
    When I am on the variant groups page
    And I click on the "Delete" action of the row which contains "SANDAL"
    And I confirm the deletion
    Then I edit the "sandal-white-37" product
    And I open the history
    And I should see history in panel:
      | version | author                                                            | property | value           |
      | 3       | Julia Stark - Julia@example.com                                   | groups   |                 |
      | 2       | Julia Stark - Julia@example.com (Comes from variant group SANDAL) | groups   | SANDAL          |
      | 1       | Admin Doe - admin@example.com                                     | SKU      | sandal-white-37 |

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
    And I press the "Save" button
    Then I should see "Products: 3"
    And I check the row "duplicate-sandal"
    And I press the "Save" button
    Then I should see "Products: 3"
    And the row "duplicate-sandal" should be checked
