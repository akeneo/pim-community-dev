@javascript
Feature: Browse attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Scenario: Successfully view, sort and filter attributes
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    Then the grid should contain 17 elements
    And I should see the columns Code, Label, Type, Scopable, Localizable and Group
    And I should see attributes sku, name, manufacturer, weather_conditions, description, price, rating, side_view, top_view, size, color, lace_color, length and number_in_stock
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, scopable, localizable and group
    Then I should be able to use the following filters:
      | filter      | value  | result                                                                                                                                                                   |
      | Code        | o      | comment, weather_conditions, description, destocking_date, top_view, color, lace_color and number_in_stock                                                               |
      | Label       | m      | comment, handmade, name, manufacturer and number_in_stock                                                                                                                |
      | Type        | Image  | side_view and top_view                                                                                                                                                   |
      | Scopable    | yes    | description                                                                                                                                                              |
      | Scopable    | no     | comment, sku, name, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length, destocking_date, handmade and number_in_stock |
      | Localizable | yes    | name and description                                                                                                                                                     |
      | Localizable | no     | comment, sku, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color, length and number_in_stock, destocking_date and handmade    |
      | Group       | Colors | color and lace_color                                                                                                                                                     |
