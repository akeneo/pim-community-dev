@javascript
Feature: Classify many products at once
  In order to easily classify products
  As a product manager
  I need to associate many products to categories at once with a form

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku       | categories        |
      | bigfoot   | summer_collection |
      | horseshoe | summer_collection |
    And a "horseshoe" product
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Add several products to categories at once
    Given I select rows bigfoot and horseshoe
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I press the "2014 collection" button
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I confirm mass edit
    And I wait for the "add_product_value" job to finish
    When I am on the products grid
    And I open the category tree
    And I select the "2014 collection" tree
    Then I should see the text "Summer collection (2)"
    And I should see the text "Winter collection (2)"

  Scenario: Move several products to categories at once
    Given I select rows bigfoot and horseshoe
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Move between categories" operation
    And I move on to the choose step
    And I choose the "Move between categories" operation
    And I select the "2014 collection" tree
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    When I am on the products grid
    And I open the category tree
    And I select the "2014 collection" tree
    Then I should see the text "Summer collection (0)"
    And I should see the text "Winter collection (2)"
