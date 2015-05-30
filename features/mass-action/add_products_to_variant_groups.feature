@javascript
Feature: Add products to variant group via a form
  In order to add products in variant group
  As a product manager
  I need to be able to add products to variant group via a form

  Scenario: Add products to a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          | color | size |
      | kickers      | blue  | 35   |
      | hiking_shoes | blue  | 38   |
      | moon_boots   | red   | 35   |
    And I am logged in as "Julia"
    And I am on the products page
    Given I mass-edit products kickers, hiking_shoes
    And I choose the "Add to a variant group" operation
    And I select the "Caterpillar boots" variant group
    And I move on to the next step
    And I wait for the "add-to-variant-group" mass-edit job to finish
    Then "caterpillar_boots" group should contain "kickers, hiking_shoes"
