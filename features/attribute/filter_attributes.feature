@javascript
Feature: Filter attributes
  In order to filter product attributes in the catalog
  As a user
  I need to be able to filter attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    Given I am on the attributes page

  Scenario: Successfully display filters
    Then I should see the filters Code, Label, Type, Scopable, Localizable and Group
    And the grid should contain 12 elements
    And I should see attributes sku, name, manufacturer, weather_conditions, description, price, rating, side_view, top_view, size, color and lace_color

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "o"
    Then the grid should contain 5 elements
    And I should see attributes weather_conditions, description, top_view, color and lace_color

  Scenario: Successfully filter by label
    Given I filter by "Label" with value "m"
    Then the grid should contain 2 elements
    And I should see attributes name and manufacturer

  Scenario: Successfully filter by type
    Given I filter by "Type" with value "Image"
    Then the grid should contain 2 elements
    And I should see attributes side_view and top_view

  Scenario: Successfully filter by scopable
    Given I filter by "Scopable" with value "yes"
    Then the grid should contain 1 element
    And I should see attribute description
    When I filter by "Scopable" with value "no"
    Then the grid should contain 11 elements

  Scenario: Successfully filter by localizable
    Given I filter by "Localizable" with value "yes"
    Then the grid should contain 2 elements
    And I should see attributes name and description
    When I filter by "Localizable" with value "no"
    Then the grid should contain 10 elements

  Scenario: Successfully filter by group
    Given I filter by "Group" with value "Colors"
    Then the grid should contain 2 elements
    And I should see attributes color and lace_color
