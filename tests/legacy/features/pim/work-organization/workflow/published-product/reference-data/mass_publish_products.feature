@javascript @published-product-feature-enabled
Feature: Publish many products at once
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku             | sole_color | sole_fabric        | family |
      | red-heels       | red        | canvas, conductive | heels  |
      | blue-sneakers   | blue       | canvas             | heels  |
      | yellow-sneakers | yellow     | conductive         | heels  |

  Scenario: Successfully publish several products with reference data
    Given I am on the published products grid
    Then the grid should contain 0 elements
    When I am on the products grid
    And I select rows red-heels, blue-sneakers and yellow-sneakers
    And I press the "Bulk actions" button
    And I choose the "Publish" operation
    And I should see the text "The 3 selected products will be published"
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    When I am on the published products grid
    Then the grid should contain 3 elements
