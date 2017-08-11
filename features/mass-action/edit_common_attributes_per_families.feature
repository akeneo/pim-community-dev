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

  @jira https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Allow editing only common attributes define from families
    Given I select rows boots and highheels
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I add available attributes Name and Weather conditions
    And I change the "Weather condition" to "Cold, Wet"
    And I change the "Name" to "Product"
    And I should see the text "Product"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product "boots" should have the following values:
      | name-en_US         | Product       |
      | weather_conditions | [cold], [wet] |
    And the product "highheels" should have the following values:
      | name-en_US | Product |

  @jira https://akeneo.atlassian.net/browse/PIM-2183
  Scenario: Allow edition on common attributes with value not in family and no value on family
    Given the following product values:
      | product | attribute    | value |
      | boots   | buckle_color | Blue  |
    When I select rows boots and high_heels
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see available attribute Buckle in group "Other"

  Scenario: Successfully update many price values at once
    Given I select rows boots and sandals
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "Price" to "100 USD"
    And I change the "Price" to "150 EUR"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the prices "Price" of products boots and sandals should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |
