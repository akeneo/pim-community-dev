@javascript
Feature: Retrieve the last selected tab
  In order to improve navigation on pim
  As a product manager
  I need to be able to retrieve the last selected tab

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully retrieve the last selected tab after a save
    Given I am on the "Sandals" family page
    And I visit the "Attributes" tab
    And I save the family
    And I should see "SKU"
    And I should see "name"
    And I should see "Manufacturer"
    And I visit the "History" tab
    And I am on the products page 
    And I am on the "Sandals" family page
    And I should see "version"
    And I should see "author"
