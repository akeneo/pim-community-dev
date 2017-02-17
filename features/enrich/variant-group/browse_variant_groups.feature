@javascript
Feature: Browse variant groups
  In order to list the existing variant groups for the catalog
  As a product manager
  I need to be able to see variant groups

  Scenario: Successfully view, sort and filter variant groups
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                     | group |
      | multi     | Multi       | pim_catalog_multiselect  | other |
      | color     | Color       | pim_catalog_simpleselect | other |
      | size      | Size        | pim_catalog_simpleselect | other |
      | dimension | Dimensions  | pim_catalog_simpleselect | other |
    And the following variant groups:
      | code              | label-en_US       | axis       | type    |
      | tshirt_akeneo     | Akeneo T-Shirt    | size,color | VARIANT |
      | mug               | Mug               | color      | VARIANT |
      | sticker_akeneo    | Akeneo Sticker    | dimension  | VARIANT |
      | mug_akeneo        | Mug Akeneo        | dimension  | VARIANT |
      | car_akeneo        | Akeneo Car        | dimension  | VARIANT |
      | boat_akeneo       | Akeneo Boat       | dimension  | VARIANT |
      | plane_akeneo      | Akeneo Plane      | dimension  | VARIANT |
      | helicopter_akeneo | Akeneo Helicopter | dimension  | VARIANT |
      | watch_akeneo      | Akeneo Watch      | dimension  | VARIANT |
      | bike_akeneo       | Akeneo Bike       | dimension  | VARIANT |
    And the following product groups:
      | code       | label-en_US | type   |
      | cross_sell | Cross Sell  | X_SELL |
    And I am logged in as "Julia"
    And I am on the variant groups page
    Then the grid should contain 10 elements
    And I should see the columns Code, Label and Axis
    And I should see groups bike_akeneo, boat_akeneo, car_akeneo, helicopter_akeneo, mug, mug_akeneo, plane_akeneo, tshirt_akeneo, sticker_akeneo, watch_akeneo
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code and Label
    And I should be able to use the following filters:
      | filter         | operator | value  | result                                                                                                                            |
      | code           | contains | mug    | mug, mug_akeneo                                                                                                                   |
      | label          | contains | Akeneo | bike_akeneo, boat_akeneo, car_akeneo, helicopter_akeneo, mug_akeneo, plane_akeneo, tshirt_akeneo, sticker_akeneo and watch_akeneo |
      | axisAttributes |          | Color  | tshirt_akeneo and mug                                                                                                             |
