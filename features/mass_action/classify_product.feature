@javascript
Feature: Classify many products at once for the tree I have access
  In order to easily classify products
  As a product manager
  I need to associate many products to categories I have access at once

  Background:
    Given the "clothing" catalog configuration
    And the following categories:
      | code    | label-en_US | parent |
      | shoes   | Shoes       |        |
      | vintage | Vintage     | shoes  |
      | trendy  | Trendy      | shoes  |
      | classy  | Classy      | shoes  |
      | boots   | Boots       |        |
    And the following category accesses:
      | category | user group | access |
      | shoes    | Manager    | view   |
      | vintage  | Manager    | view   |
      | trendy   | Manager    | view   |
      | classy   | Manager    | view   |
    And the following products:
      | sku     | categories      |
      | rangers | 2014_collection |
      | loafer  | 2014_collection |
    And I am logged in as "Julia"
    And I am on the products page

  # TODO: Un-skip this scenario in PIM-4251
  @skip
  Scenario: Classify many products at once
    Given I mass-edit products rangers and loafer
    And I choose the "Classify products in categories" operation
    Then I should not see "Boots"
    And I should not see "Master catalog"
    When I select the "Shoes" tree
    And I expand the "Shoes" category
    And I click on the "Vintage" category
    And I click on the "Classy" category
    And I move on to the next step
    And I wait for the "classify" mass-edit job to finish
    And I am on the products page
    And I select the "Shoes" tree
    Then I should see "Vintage (2)"
    And I should see "Classy (2)"

