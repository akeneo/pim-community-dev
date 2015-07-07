@javascript
Feature: Submit a modification on reference data for a product draft
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values

  Background:
    Given a "footwear" catalog configuration
    And the following reference data:
      | type   | code     |
      | fabric | neoprene |
      | fabric | wool     |
      | fabric | kevlar   |
      | color  | red      |
      | color  | green    |
    And the product:
      | sku         | my-vans         |
      | categories  | winter_boots    |
      | sole_color  | red             |
      | sole_fabric | kevlar,neoprene |
    And I am logged in as "Mary"
    And I edit the "my-vans" product
    And I visit the "Other" group

  @skip-pef
  Scenario: Successfully propose an simple select reference data change
    Given I change the "Sole color" to "[green]"
    And I save the product
    Then the product "my-vans" should have the following values:
      | sole_color | [red] |
    But the field Sole color should contain "[green]"
    And I should see that Sole color is a modified value

  @skip-pef
  Scenario: Successfully propose a multi reference data change
    Given I change the "Sole fabric" to "[kevlar],[wool]"
    And I save the product
    Then the product "my-vans" should have the following values:
      | sole_fabric | [neoprene], [kevlar] |
    But the field Sole fabric should contain "[kevlar], [wool]"
    And I should see that Sole fabric is a modified value
