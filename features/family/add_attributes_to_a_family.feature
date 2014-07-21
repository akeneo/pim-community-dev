@javascript @info https://akeneo.atlassian.net/browse/PIM-355
Feature: Add attribute to a family
  In order to validate exported attributes
  As an administrator
  I need to be able to define which attributes belong to a family

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully list available grouped attributes
    Given I am on the "Sandals" family page
    And I visit the "Attributes" tab
    Then I should see available attribute Weather conditions in group "Product information"
    And I should see available attribute Lace color in group "Colors"
    And I should see available attribute Top view in group "Media"

  Scenario: Successfully display all grouped family's attributes
    Given I am on the "Sneakers" family page
    And I visit the "Attributes" tab
    Then I should see attributes "SKU, Name, Manufacturer, Weather conditions and Description" in group "Product information"
    And I should see attributes "Price and Rating" in group "Marketing"
    And I should see attributes "Side view and Top view" in group "Media"
    And I should see attribute "Size" in group "Sizes"
    And I should see attributes "Color and Lace color" in group "Colors"

  @info https://akeneo.atlassian.net/browse/PIM-244
  Scenario: Successfully add an attribute to a family
    Given I am on the "Sandals" family page
    And I visit the "Attributes" tab
    And I add available attributes Weather conditions and Top view
    Then I should see attributes "SKU, Name, Manufacturer, Weather conditions and Description" in group "Product information"
    And I should see attributes "Side view and Top view" in group "Media"
