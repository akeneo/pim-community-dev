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
      | filter      | value  | result                                                                                                                                                                                                                                |
      | Code        | o      | comment, weather_conditions, description, destocking_date, top_view, color, lace_color, number_in_stock, heel_color, sole_color, sole_fabric and cap_color                                                                            |
      | Label       | m      | comment, handmade, name, manufacturer and number_in_stock                                                                                                                                                                             |
      | Type        | Image  | side_view and top_view                                                                                                                                                                                                                |
      | Scopable    | yes    | description, cap_color and lace_fabric                                                                                                                                                                                                |
      | Scopable    | no     | comment, sku, name, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length, destocking_date, handmade, number_in_stock, heel_color, sole_color, sole_fabric, rate_sale, weight and 123 |
      | Localizable | yes    | name, description, cap_color and lace_fabric                                                                                                                                                                                          |
      | Localizable | no     | comment, sku, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length and number_in_stock, destocking_date, handmade, heel_color, sole_color, sole_fabric, rate_sale, weight and 123    |
      | Group       | Colors | color and lace_color                                                                                                                                                                                                                  |
