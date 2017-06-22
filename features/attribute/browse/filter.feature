@javascript
Feature: Filter attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario Outline: Successfully filter attributes
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter        | operator | value  | result                                                                                                                                                                                                                                        | count |
      | code          | contains | o      | comment, volume, weather_conditions, description, destocking_date, top_view, color, lace_color, number_in_stock, heel_color, sole_color, sole_fabric and cap_color                                                                            | 13    |
      | type          | in list  | Image  | side_view and top_view                                                                                                                                                                                                                        | 2     |
      | scopable      |          | yes    | description, cap_color and lace_fabric                                                                                                                                                                                                        | 3     |
      | scopable      |          | no     | comment, sku, volume, name, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length, destocking_date, handmade, number_in_stock, heel_color, sole_color, sole_fabric, rate_sale, weight and 123 | 23    |
      | localizable   |          | yes    | name, description, cap_color and lace_fabric                                                                                                                                                                                                  | 4     |
      | localizable   |          | no     | comment, volume, sku, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length and number_in_stock, destocking_date, handmade, heel_color, sole_color, sole_fabric, rate_sale, weight and 123    | 22    |
      | group         | in list  | Colors | color and lace_color                                                                                                                                                                                                                          | 2     |

  Scenario: Successfully search on label
    When I search "m"
    Then the grid should contain 6 elements
    And I should see entities comment, volume, handmade, name, manufacturer and number_in_stock
