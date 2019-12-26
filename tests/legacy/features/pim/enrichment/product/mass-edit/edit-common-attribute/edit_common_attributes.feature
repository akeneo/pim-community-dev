@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

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

  @jira https://akeneo.atlassian.net/browse/PIM-6273
  Scenario: Successfully remove product attribute fields
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    When I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    Then I should see a remove link next to the "Name" field
    When I remove the "Name" attribute
    Then I should not see the "Name" field
    And I should not see a remove link next to the "Name" field

  @critical
  Scenario: Successfully update many text values at once
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    Then I should see a remove link next to the "Name" field
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the english localizable value name of "boots" should be "boots"
    And the english localizable value name of "sandals" should be "boots"
    And the english localizable value name of "sneakers" should be "boots"

  @critical
  Scenario: Successfully update many multi-valued values at once
    Given I am on the products grid
    And I select rows boots and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |
