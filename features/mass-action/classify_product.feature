@javascript
Feature: Classify many products at once
  In order to easily classify products
  As a product manager
  I need to associate many products to categories at once with a form

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku       | categories        |
      | bigfoot   | summer_collection |
      | horseshoe | summer_collection |
    And a "horseshoe" product
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Add several products to categories at once
    Given I select rows bigfoot and horseshoe
    And I press "Move products in categories" on the "Bulk Actions" dropdown button
    And I choose the "Classify products in categories" operation
    And I press the "Back" button
    And I choose the "Classify products in categories" operation
    And I press the "2014 collection" button
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I move on to the next step
    And I wait for the "classify-add" mass-edit job to finish
    When I am on the products page
    And I select the "2014 collection" tree
    Then I should see the text "Summer collection (2)"
    And I should see the text "Winter collection (2)"

  Scenario: Move several products to categories at once
    Given I select rows bigfoot and horseshoe
    And I press "Move products in categories" on the "Bulk Actions" dropdown button
    And I choose the "Move products to categories" operation
    And I press the "Back" button
    And I choose the "Move products to categories" operation
    And I select the "2014 collection" tree
    And I expand the "2014_collection" category
    And I click on the "winter_collection" category
    And I move on to the next step
    And I wait for the "classify-move" mass-edit job to finish
    When I am on the products page
    And I select the "2014 collection" tree
    Then I should see the text "Summer collection (0)"
    And I should see the text "Winter collection (2)"
