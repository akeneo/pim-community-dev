@javascript
Feature: Add products to variant group via a form
  In order to add products in variant group
  As a product manager
  I need to be able to add products to variant group via a form

  Scenario: Add products to a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | kickers      |
      | hiking_shoes |
      | moon_boots   |
    And I am logged in as "Julia"
    And I am on the products page
    Given I mass-edit products kickers, hiking_shoes
    And I choose the "Add to a variant group" operation
    And I select the "Caterpillar boots" variant group
    And I press the "Next" button
    And I apply the following mass-edit operation with the given configuration:
      | operation            | filters                                                                  | actions                                                    |
      | add-to-variant-group | [{"field":"sku", "operator":"IN", "value": ["kickers", "hiking_shoes"]}] | [{"field": "variant_group", "value": "caterpillar_boots"}] |
    Then "caterpillar_boots" group should contain "kickers, hiking_shoes"
