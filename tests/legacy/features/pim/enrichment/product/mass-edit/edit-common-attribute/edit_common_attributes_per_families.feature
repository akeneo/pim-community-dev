@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code         | label-en_US | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | weight       | Weight      | pim_catalog_metric | Weight        | GRAM                | other | 0                | 0                |
      | heel_height  | Heel Height | pim_catalog_metric | Length        | CENTIMETER          | other | 0                | 0                |
      | buckle_color | Buckle      | pim_catalog_text   |               |                     | other |                  |                  |
    And the following family:
      | code       | attributes                                                                                                        |
      | high_heels | sku,name,description,price,rating,size,color,manufacturer,heel_height,buckle_color                                |
      | boots      | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sneakers   | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sandals    | sku,name,manufacturer,description,price,rating,side_view,size,color,weight,heel_height                            |
    And the following products:
      | sku            | family     | color | heel_height   | groups |
      | boots          | boots      |       |               |        |
      | sneakers       | sneakers   |       |               |        |
      | sandals        | sandals    |       |               |        |
      | pump           |            |       |               |        |
      | highheels      | high_heels | red   | 12 CENTIMETER |        |
      | blue_highheels | high_heels | blue  | 12 CENTIMETER |        |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully update many price values at once
    Given I select rows boots and sandals
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Price attribute
    And I change the "Price" to "100 USD"
    And I change the "Price" to "150 EUR"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the prices "Price" of products boots and sandals should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |
