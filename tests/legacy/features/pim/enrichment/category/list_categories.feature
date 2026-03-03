@javascript
Feature: List categories
  In order to be able to see the categories in my catalog
  As a product manager
  I need to be able to list existing categories

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Navigate to edit category page
    Given I am on the categories page
    When I follow the "2014 collection" category tree
    Then I should see the text "2014 collection"
    And I follow the "Summer collection" category
    Then the field Code should contain "summer_collection"
