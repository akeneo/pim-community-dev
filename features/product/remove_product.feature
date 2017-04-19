@javascript
Feature: Remove a product
  In order to delete an unnecessary product from my PIM
  As a product manager
  I need to be able to remove a product

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | weight      | Weight      | pim_catalog_metric | Weight        | GRAM                | other | 0                | 0                |
      | heel_height | Heel Height | pim_catalog_metric | Length        | CENTIMETER          | other | 0                | 0                |
    And the following family:
      | code       | attributes                                                                                                        |
      | high_heels | sku,name,description,price,rating,size,color,manufacturer,heel_height                                             |
      | boots      | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sneakers   | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sandals    | sku,name,manufacturer,description,price,rating,side_view,size,color,weight                                        |
    And the following products:
      | sku   | family     |
      | boots | high_heels |
    And I am logged in as "Julia"

  Scenario: Successfully delete a product from the grid
    Given I am on the products page
    Then I should see product boots
    When I click on the "Delete the product" action of the row which contains "boots"
    Then I should see the text "Delete confirmation"
    When I confirm the removal
    Then I should not see product boots

  Scenario: Successfully delete a product from the edit form
    Given I am on the "boots" product page
    And I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    When I confirm the removal
    Then I should not see product boots
