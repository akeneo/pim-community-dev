@javascript
Feature: Add products with reference data to a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to add products to a variant group

  Background:
    Given the "footwear" catalog configuration
    And the following "sole_color" attribute reference data: Red, Yellow, Cyan, Magenta
    And the following "heel_color" attribute reference data: Green, Light green, Blue, Black, White
    Given the following family:
      | code       | attributes          |
      | my_sandals | sku,size,sole_color |
    And the following products:
      | sku           | family     | categories        | size | sole_color |
      | sandal-red-37 | my_sandals | winter_collection | 37   | Red        |
      | sandal-red-38 | my_sandals | winter_collection | 38   | Red        |
      | sandal-red-39 | my_sandals | winter_collection | 39   | Red        |
    And the following variant groups:
      | code   | label-en_US | axis            | type    |
      | SANDAL | Sandal      | size,sole_color | VARIANT |
    And I am logged in as "Julia"

  Scenario: Successfully add products in variant groups, products are updated with variant group values
    Given I am on the "SANDAL" variant group page
    And the grid should contain 3 elements
    And I should see products sandal-red-37, sandal-red-38, sandal-red-39
    And I check the row "sandal-red-37"
    And I check the row "sandal-red-38"
    When I press the "Save" button
    Then the row "sandal-red-37 and sandal-red-38" should be checked

  Scenario: Do not see a product already present in another variant group
    Given the following family:
      | code       | attributes                     |
      | high_heels | sku,sole_color,heel_color,size |
    And the following variant groups:
      | code       | label-en_US | axis                       | type    |
      | HIGH_HEELS | High heels  | size,sole_color,heel_color | VARIANT |
    And the following products:
      | sku            | family     | categories        | size | sole_color | heel_color |
      | heel-yellow-37 | high_heels | winter_collection | 37   | Yellow     | Black      |
      | heel-yellow-38 | high_heels | winter_collection | 38   | Yellow     | Black      |
      | heel-yellow-39 | high_heels | winter_collection | 39   | Yellow     | Black      |
    And I am on the "HIGH_HEELS" variant group page
    Then the grid should contain 3 elements
    And I should see products heel-yellow-37, heel-yellow-38, heel-yellow-39
    And I am on the "SANDAL" variant group page
    Then the grid should contain 6 elements
    And I check the row "heel-yellow-37"
    And I press the "Save" button
    And I should not see the text "There are unsaved changes"
    # TODO: see with @nidup => temporary fix (broken since the deferred explicit persist of Doctrine)
    And I press the "Save" button
    And I should not see the text "There are unsaved changes"
    And I am on the "HIGH_HEELS" variant group page
    Then the grid should contain 2 elements
    And I should see products heel-yellow-38, heel-yellow-39
