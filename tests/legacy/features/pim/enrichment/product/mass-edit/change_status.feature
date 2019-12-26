@javascript
Feature: Configure action to change status of many products at once
  In order to configure mass edit change status on many products
  As a product manager
  I need to be able to configure mass edit action via a form

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Configure the operation to enable many products at once
    Given a disabled "boat" product
    And a disabled "jet-ski" product
    And I am on the products grid
    When I select rows boat and jet-ski
    And I press the "Bulk actions" button
    And I choose the "Change status" operation
    And I enable the products
    And I wait for the "update_product_value" job to finish
    Then product "boat" should be enabled
    And product "jet-ski" should be enabled
