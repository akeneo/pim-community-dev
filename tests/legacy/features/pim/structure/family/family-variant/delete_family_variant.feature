@javascript
Feature: Delete a family variant
  In order to manage families variants in the catalog
  As an administrator
  I need to be able to delete family variants

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully delete a family variant used by product models from the grid
    Given the following family variants:
      | code                 | family   | variant-axes_1 | variant-attributes_1 |
      | empty_family_variant | clothing | color          | description          |
    And I am on the "Clothing" family page
    And I visit the "Variants" tab
    When I click on the "Delete" action of the row which contains "empty_family_variant"
    And I confirm the deletion
    Then I should not see the text "empty_family_variant"
