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
    And the following variant groups:
      | code          | label-en_US   | axis  | type    |
      | variant_heels | Variant Heels | color | VARIANT |
    And the following variant group values:
      | group         | attribute   | value         |
      | variant_heels | heel_height | 12 CENTIMETER |
    And the following products:
      | sku            | family     | color | groups        |
      | boots          | boots      |       |               |
      | sneakers       | sneakers   |       |               |
      | sandals        | sandals    |       |               |
      | pump           |            |       |               |
      | highheels      | high_heels | red   | variant_heels |
      | blue_highheels | high_heels | blue  | variant_heels |
    And I am logged in as "Julia"
    And I am on the products grid

  @jira https://akeneo.atlassian.net/browse/PIM-3282, https://akeneo.atlassian.net/browse/PIM-3880
  Scenario: Successfully mass edit products on the non default channel
    Given the following product values:
      | product   | attribute                | value                   |
      | boots     | description-en_US-tablet | A beautiful description |
      | boots     | weight                   | 500 GRAM                |
      | sneakers  | description-en_US-tablet | A beautiful description |
      | sneakers  | weight                   | 500 GRAM                |
      | sandals   | weight                   | 500 GRAM                |
      | pump      | weight                   | 500 GRAM                |
      | highheels | weight                   | 500 GRAM                |
    When I show the filter "description"
    And I switch the scope to "Tablet"
    And I filter by "description" with operator "contains" and value "A beautiful description"
    And I select all entities
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the metric "Weight" of products boots and sneakers should be "600"
    And the metric "Weight" of products sandals, pump and highheels should be "500"
