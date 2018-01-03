@javascript
Feature: Delete many products but not the product models
  In order to secure integrity of the products catalog
  As a user
  I need to be able to mass delete only products within a selection of products and product models

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: Successfully mass delete a selection of only products within a selection of products and product models.
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I select rows model-tshirt-divided-crimson-red, tshirt-unique-size-crimson-red and running-shoes-m-crimson-red
    And I press the "Delete" button
    And I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    And I refresh current page
    Then I should not see products tshirt-unique-size-crimson-red and running-shoes-m-crimson-red
    And I should see the product models model-tshirt-divided-crimson-red

  @jira https://akeneo.atlassian.net/browse/PIM-6968
  Scenario: Successfully mass delete products when selected all
    Given I select rows 1111111171
    And I select all entities
    And I press the "Delete" button and wait for modal
    When I confirm the removal
    Then I should not see products 1111111171 and 1111111172
    And I filter by "family" with operator "in list" and value "Clothing"
    And I should see the product models amor
