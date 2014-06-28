@javascript
Feature: Classify many products at once
  In order to easily classify products
  As a product manager
  I need to associate many products to categories at once

  Background:
    Given the "footwear" catalog configuration
    And a "bigfoot" product
    And a "horseshoe" product
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Classify many products at once
    Given I mass-edit products bigfoot and horseshoe
    And I choose the "Classify products in categories" operation
    And I select the "2014 collection" tree
    And I expand the "2014 collection" category
    And I click on the "Winter collection" category
    And I click on the "Summer collection" category
    When I move on to the next step
    And I am on the products page
    And I select the "2014 collection" tree
    Then I should see "Summer collection (2)"
    And I should see "Winter collection (2)"

