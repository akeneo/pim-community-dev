@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @skip
  Scenario: Successfully revert the status of a product (disabled)
    Given an enabled "boat" product
    And I am on the "boat" product page
    And I disable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: boat"
    Then product "boat" should be enabled

  @skip
  Scenario: Successfully revert the status of a product (enable)
    Given a disabled "boat" product
    And I am on the "boat" product page
    And I enable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: boat"
    Then product "boat" should be disabled

  @skip
  Scenario: Successfully revert the family of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    And I am on the products page
    Then I mass-edit products jean
    And I choose the "Change the family of products" operation
    And I change the Family to "Jackets"
    And I move on to the next step
    Then the family of product "jean" should be "jackets"
    And I am on the "jean" product page
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then the family of product "jean" should be "pants"

  @skip
  Scenario: Successfully revert the category of a product
    Given the following product:
      | sku     | categories        |
      | sandals | winter_collection |
    And I edit the "sandals" product
    And I visit the "Categories" tab
    And I select the "2014 collection" tree
    And I expand the "2014 collection" category
    And I click on the "Winter collection" category
    And I click on the "Summer collection" category
    And I press the "Save" button
    Then I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: sandals"
    Then the category of "sandals" should be "winter_collection"


