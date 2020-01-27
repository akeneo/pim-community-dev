@javascript
Feature: Unpublish many products at once
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish several products at the same time

  Background:
    Given a "footwear" catalog configuration
    And the following published product:
      | sku             | sole_color | sole_fabric        | family |
      | red-heels       | red        | canvas, conductive | heels  |
      | blue-sneakers   | blue       | canvas             | heels  |
      | yellow-sneakers | yellow     | conductive         | heels  |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully unpublish several products with reference data
    And I am on the published products grid
    Then the grid should contain 3 elements
    And I select rows red-heels and blue-sneakers
    And I press the "Bulk actions" button
    When I choose the "Unpublish" operation
    Then I should see the text "The 2 selected products will be unpublished"
    When I confirm mass edit
    And I wait for the "unpublish_product" job to finish
    And I am on the published products grid
    Then the grid should contain 1 element
