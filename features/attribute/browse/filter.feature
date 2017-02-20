@javascript
Feature: Filter attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully filter attributes
    Then I should be able to use the following filters:
      | filter        | operator | value  | result                                                                                                                                                                                                                                        |
      | code          | contains | o      | comment, volume, weather_conditions, description, destocking_date, top_view, color, lace_color, number_in_stock, heel_color, sole_color, sole_fabric and cap_color                                                                            |
      | label         | contains | m      | comment, volume, handmade, name, manufacturer and number_in_stock                                                                                                                                                                             |
      | type          | in list  | Image  | side_view and top_view                                                                                                                                                                                                                        |
      | scopable      |          | yes    | description, cap_color and lace_fabric                                                                                                                                                                                                        |
      | scopable      |          | no     | comment, sku, volume, name, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length, destocking_date, handmade, number_in_stock, heel_color, sole_color, sole_fabric, rate_sale, weight and 123 |
      | localizable   |          | yes    | name, description, cap_color and lace_fabric                                                                                                                                                                                                  |
      | localizable   |          | no     | comment, volume, sku, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length and number_in_stock, destocking_date, handmade, heel_color, sole_color, sole_fabric, rate_sale, weight and 123    |
      | group         | in list  | Colors | color and lace_color                                                                                                                                                                                                                          |
