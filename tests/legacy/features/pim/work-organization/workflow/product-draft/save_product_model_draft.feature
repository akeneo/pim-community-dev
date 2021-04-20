Feature: Save a product model draft
  In order to update the product model even if I'm not owner
  As a redactor
  I need to be able to save a product model draft

  @javascript
  Scenario: Successfully save a product model draft via the PEF
    Given the "catalog_modeling" catalog configuration
    And the following product category accesses:
      | product category   | user group | access |
      | master_women_shoes | Redactor   | edit   |
      | master_women_shoes | Manager    | own    |
    When I am logged in as "Mary"
    And I edit the "brookspink" product model
    And I change the "Supplier" to "mongo"
    Then I save the product
    And I press the Send for approval button
    And I logout
    When I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the following proposals:
      | product     | author | attribute | original | new   |
      | Brooks pink | Mary   | supplier  |          | mongo |
