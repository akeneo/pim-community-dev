@javascript
Feature: Browse attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Scenario: Successfully view, sort and filter attributes
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    Then the grid should contain 14 elements
    And I should see the columns Code, Label, Type, Scopable, Localizable and Group
    And I should see attributes sku, name, manufacturer, weather_conditions, description, price, rating, side_view, top_view, size, color, lace_color and length
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, scopable, localizable and group
    Then I should be able to use the following filters:
      | filter      | value  | result                                                                                                                       |
      | Code        | o      | comment, weather_conditions, description, top_view, color and lace_color                                                     |
      | Label       | m      | comment, name and manufacturer                                                                                               |
      | Type        | Image  | side_view and top_view                                                                                                       |
      | Scopable    | yes    | description                                                                                                                  |
      | Scopable    | no     | comment, sku, name, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color and length |
      | Localizable | yes    | name and description                                                                                                         |
      | Localizable | no     | comment, sku, manufacturer, weather_conditions, price, rating, side_view, top_view, size, color, lace_color and length       |
      | Group       | Colors | color and lace_color                                                                                                         |
