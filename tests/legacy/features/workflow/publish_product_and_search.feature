@javascript
Feature: Publish a product and search for the published product
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish a product and search it later

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully search for a published product even if the original product has been modified
    Given the following product:
      | sku       | family  | name-en_US | manufacturer |
      | my-jacket | jackets | Jackets    | desigual     |
    And I edit the "my-jacket" product
    And I press the secondary action "Publish"
    And I confirm the publishing
    And I fill in the following information:
      | Manufacturer | Volcom |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the published products grid
    And I show the filter "manufacturer"
    And I filter by "manufacturer" with operator "in list" and value "Desigual"
    Then the grid should contain 1 elements
    And I should see product my-jacket
