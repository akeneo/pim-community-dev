@javascript
Feature: Add products to a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to add products to a variant group

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU             | sandal-brown-37 |
      | Choose a family | sandals         |
    And I press the "Save" button in the popin
    And I wait to be on the "sandal-brown-37" product page
    And I fill in the following information:
      | size       | 37       |
      | color      | brown    |
      | name-en_US | old name |
    And I save the product
#    And the following product groups:
#      | code   | label  | axis        | type    |
#      | SANDAL | Sandal | size, color | VARIANT |
#    And the following variant group values:
#      | group  | attribute    | value       | locale | scope |
#      | SANDAL | manufacturer | Converse    |        |       |
#      | SANDAL | name         | EN name     | en_US  |       |
#      | SANDAL | comment      | New comment |        |       |
#    And I am logged in as "Julia"

  Scenario: Successfully add products in variant groups, products are updated with variant group values
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-brown-37, sandal-brown-38, sandal-brown-39
    And I check the row "sandal-brown-37"
    And I check the row "sandal-brown-38"
    And I press the "Save" button
    Then the rows "sandal-brown-37 and sandal-brown-38" should be checked
    And the product "sandal-brown-37" should have the following value:
      | name-en_US   | EN name     |
      | comment      | New comment |
      | manufacturer | [Converse]  |
    And the product "sandal-brown-38" should have the following value:
      | name-en_US   | EN name     |
      | comment      | New comment |
      | manufacturer | [Converse]  |
    And the product "sandal-brown-39" should have the following value:
      | name-en_US | old name |

  Scenario: Successfully add products in variant groups, history should be updated with a variant group context
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-brown-37
    And I check the row "sandal-brown-37"
    And I press the "Save" button
    Then the row "sandal-brown-37" should be checked
    When I edit the "sandal-brown-37" product
    And I open the history
    Then I should see history:
      | version | author                                                            | property | value           |
      | 2       | Julia Stark - Julia@example.com (Comes from variant group SANDAL) | groups   | SANDAL          |
      | 1       | Admin Doe - admin@example.com                                     | SKU      | sandal-brown-37 |

  Scenario: Successfully delete a variant group, product history should be updated without context
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    And I should see products sandal-brown-39
    And I check the row "sandal-brown-39"
    And I press the "Save" button
    Then the row "sandal-brown-39" should be checked
    When I am on the variant groups page
    And I click on the "Delete" action of the row which contains "SANDAL"
    And I confirm the deletion
    Then I edit the "sandal-brown-39" product
    And I open the history
    And I should see history in panel:
      | version | author                                                            | property | value           |
      | 3       | Julia Stark - Julia@example.com                                   | groups   |                 |
      | 2       | Julia Stark - Julia@example.com (Comes from variant group SANDAL) | groups   | SANDAL          |
      | 1       | Admin Doe - admin@example.com                                     | SKU      | sandal-brown-39 |

  @jira https://akeneo.atlassian.net/browse/PIM-3736
  Scenario: Reject product addition in a variant group, products count should be correct
    Given the following products:
      | sku              | family  | categories        | size | color | name-en_US |
      | sandal-brown-37  | sandals | winter_collection | 37   | brown | old name   |
      | sandal-brown-38  | sandals | winter_collection | 38   | brown | old name   |
      | sandal-brown-39  | sandals | winter_collection | 39   | brown | old name   |
      | duplicate-sandal | sandals | winter_collection | 39   | brown | old name   |
    And I am on the "SANDAL" variant group page
    Then the grid should contain 4 elements
    And I check the row "sandal-brown-37"
    And I check the row "sandal-brown-38"
    And I check the row "sandal-brown-39"
    And I press the "Save" button
    Then I should see "Products: 3"
    And I check the row "duplicate-sandal"
    And I press the "Save" button
    Then I should see "Products: 3"
    And the row "duplicate-sandal" should be checked
