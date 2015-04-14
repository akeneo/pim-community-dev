@javascript
Feature: Submit a modification on reference data for a product draft
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values

  Background:
    Given a "footwear" catalog configuration
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the product:
      | sku         | my-vans         |
      | categories  | winter_boots    |
      | sole_color  | Red             |
      | sole_fabric | Kevlar,Neoprene |
    And I am logged in as "Mary"
    And I edit the "my-vans" product
    And I visit the "Other" group

  Scenario: Successfully propose an simple select reference data change
    Given I change the "Sole color" to "[Green]"
    And I save the product
    Then the product "my-vans" should have the following values:
      | sole_color | [Red] |
    But the field Sole color should contain "[Green]"
    And I should see that Sole color is a modified value

  Scenario: Successfully propose a multi reference data change
    Given I change the "Sole fabric" to "[Kevlar],[Wool]"
    And I save the product
    Then the product "my-vans" should have the following values:
      | sole_fabric | [Neoprene], [Kevlar] |
    But the field Sole fabric should contain "[Kevlar], [Wool]"
    And I should see that Sole fabric is a modified value
