@javascript
Feature: Classify a product
  In order to classify products
  As a product manager
  I need to associate a product to categories

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    |
      | tea    |
      | coffee |
    And I am logged in as "Julia"

  Scenario: Associate a product to categories
    Given I edit the "tea" product
    When I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I click on the "summer_collection" category
    And I click on the "winter_collection" category
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the categories of the product "tea" should be "summer_collection and winter_collection"
