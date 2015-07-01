@javascript
Feature: Assign assets to a product
  In order to assign assets to a product
  As a product manager
  I need to be able to link multiple assets to a product

  Background:
    Given the "clothing" catalog configuration
    And the following assets:
    | code       | tags             | description            | end of use at |
    | blue_shirt | solid_color, men | A beautiful blue shirt | now           |
    | red_shirt  | solid_color, men | A beautiful red shirt  | now           |
    And the following products:
      | sku   |
      | shirt |
    And I am logged in as "Julia"

  Scenario:
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I check the row "AC1237"
    And I check the row "AC2230"
    Then the asset basket should contain AC1237, AC2230
