@javascript
Feature: Add products to variant group via a form
  In order to add products in variant group
  As a product manager
  I need to be able to add products to variant group via a form

  @skip
  Scenario: Add products to a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          | color | size |
      | kickers      | blue  | 35   |
      | hiking_shoes | blue  | 38   |
      | moon_boots   | red   | 35   |
    And I am logged in as "Julia"
    And I am on the products grid
    Given I select rows kickers, hiking_shoes
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Add to a variant group" operation
    And I select the "Caterpillar boots" variant group
    And I confirm mass edit
    And I wait for the "add_to_variant_group" job to finish
    Then "caterpillar_boots" group should contain "kickers, hiking_shoes"
