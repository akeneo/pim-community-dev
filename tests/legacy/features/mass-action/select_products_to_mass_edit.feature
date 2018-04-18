@javascript
Feature: When I mass edit I should be able to see how many items will be edited

  Background:
    Given a "footwear" catalog configuration
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
      | sku       | family     |
      | boots     | boots      |
      | sneakers  | sneakers   |
      | sandals   | sandals    |
      | pump      |            |
      | highheels | high_heels |
      | shoe_1    | high_heels |
      | shoe_2    | high_heels |
      | shoe_3    | high_heels |
      | shoe_4    | high_heels |
      | shoe_5    | high_heels |
      | shoe_6    | high_heels |
      | shoe_7    | high_heels |
      | shoe_8    | high_heels |
      | shoe_9    | high_heels |
      | shoe_10   | high_heels |
      | shoe_11   | high_heels |
      | shoe_12   | high_heels |
      | shoe_13   | high_heels |
      | shoe_14   | high_heels |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully count the number of mass-edited items when click on all products
    Given I sort by "ID" value descending
    And I select rows boots
    And I select all entities
    When I press the "Bulk actions" button
    Then I should see the text "Select your action"

  Scenario: Successfully count the number of mass-edited items by select them one by one
    Given I sort by "ID" value descending
    When I select rows boots
    And I press the "Bulk actions" button
    Then I should see the text "Select your action"

  Scenario: Successfully count the number of mass-edited items when using filters and select all action
    Given the following product values:
      | product   | attribute                | value                   |
      | boots     | description-en_US-tablet | A beautiful description |
      | boots     | weight                   | 500 GRAM                |
      | sneakers  | description-en_US-tablet | A beautiful description |
      | sneakers  | weight                   | 500 GRAM                |
      | sandals   | weight                   | 500 GRAM                |
      | pump      | weight                   | 500 GRAM                |
      | highheels | weight                   | 500 GRAM                |
    And I show the filter "description"
    And I switch the scope to "Tablet"
    And I filter by "description" with operator "contains" and value "A beautiful description"
    And I select rows sneakers
    And I select all entities
    When I press the "Bulk actions" button
    Then I should see the text "Select your action"
