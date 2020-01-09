@javascript
Feature: Add attributes by attribute groups to a family
  In order to configure families
  As an administrator
  I need to be able to add attributes to families by attribute group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  @info https://akeneo.atlassian.net/browse/PIM-6095
  Scenario: Successfully add attributes by attribute groups to a family
    Given I am on the "Sandals" family page
    And I visit the "Attributes" tab
    And I should see available attribute group "Product information, Marketing, Colors, Media and Other"
    And I add attributes by group "Media, Product information and Marketing"
    Then I should see attributes "Price, Rate of sale and Rating" in group "Marketing"
    And I should see attributes "SKU, Name, Manufacturer, Weather conditions and Description" in group "Product information"
    And I should see attributes "Length, Volume and Weight" in group "Product information"
    And I should see attributes "Side view and Top view" in group "Media"
