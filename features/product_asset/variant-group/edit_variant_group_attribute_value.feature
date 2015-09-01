@javascript
Feature: Editing attribute values of a variant group also updates products with an assets collection
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to change reference data attribute values of a variant group

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku    | groups     | main_color | size |
      | jacket | hm_jackets | black      | M    |
    And I am logged in as "Julia"

  Scenario: Add an assets collection attribute to a variant group
    Given I am on the "hm_jackets" variant group page
    And I visit the "Attributes" tab
    When I add available attributes gallery
    And I visit the "Media" group
    And I change the "gallery" to "[man_wall], [akene]"
    And I save the variant group
    And I am on the "jacket" product page
    And I visit the "Media" group
    Then the "gallery" asset gallery should contain man_wall, akene
