@javascript
Feature: Edit asset collection of many products at once
  In order to update many products with the same assets
  As a product manager
  I need to be able to edit asset collection of many products at once

  Background:
    Given the "clothing" catalog configuration
    And the following family:
      | code       | attributes     |
      | high_heels | sku,front_view |
    And the following products:
      | sku      | family     |
      | boots    | high_heels |
      | sneakers | high_heels |
      | sandals  | high_heels |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Allow editing all attributes on configuration screen
    Given I select rows boots, sneakers and sandals
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Front view attribute
    And I start to manage assets for "Front view"
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain paint, machine
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And I am on the "boots" product page
    And I visit the "Media" group
    Then the "Front view" asset gallery should contain paint, machine
