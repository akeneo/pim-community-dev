@javascript
Feature: Mass add product value to products at once via a form
  In order to easily add value to products
  As a product manager
  I need to be able to add product values to many products at once via a form without erasing existing values

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: It adds value to multi select attributes
    Given I am on the products grid
    And I select rows Hat and Scarf
    And I press the "Bulk actions" button
    And I choose the "Add attributes values" operation
    And I display the Collection attribute
    And I change the "Collection" to "Autumn 2016, Spring 2015"
    And I confirm mass edit
    And I wait for the "add_attribute_value" job to finish
    Then the options "collection" of products 1111111240 and 1111111292 should be:
      | value       |
      | autumn_2016 |
      | spring_2015 |
    When I am on the products grid
    And I select rows Hat and Scarf
    And I press the "Bulk actions" button
    And I choose the "Add attributes values" operation
    And I display the Collection attribute
    And I change the "Collection" to "Summer 2017"
    And I confirm mass edit
    And I wait for the "add_attribute_value" job to finish
    Then the options "collection" of products 1111111240 and 1111111292 should be:
      | value       |
      | autumn_2016 |
      | spring_2015 |
      | summer_2017 |
