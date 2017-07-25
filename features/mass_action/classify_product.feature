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
    And the following product category accesses:
      | product category | user group | access |
      | shoes            | Manager    | view   |
      | vintage          | Manager    | view   |
      | trendy           | Manager    | view   |
      | classy           | Manager    | view   |
      | boots            | IT support | none   |
      | 2014_collection  | Manager    | own    |
    And the following products:
      | sku     | categories      |
      | rangers | 2014_collection |
      | loafer  | 2014_collection |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Add several products to categories at once
    Given I select rows rangers and loafer
    And I press "Move products in categories" on the "Bulk Actions" dropdown button
    And I choose the "Classify products in categories" operation
    Then I should not see "Boots"
    And I should not see "Master catalog"
    When I select the "Shoes" tree
    And I expand the "shoes" category
    And I click on the "vintage" category
    And I click on the "classy" category
    And I move on to the next step
    And I wait for the "add_product_value" job to finish
    And I am on the products page
    And I select the "Shoes" tree
    Then I should see the text "2014 collection (2)"
    Then I should see the text "Vintage (2)"
    And I should see the text "Classy (2)"

  Scenario: Move several products to categories at once
    Given I select rows rangers and loafer
    And I press "Move products in categories" on the "Bulk Actions" dropdown button
    And I choose the "Move products to categories" operation
    Then I should not see "Boots"
    And I should not see "Master catalog"
    When I select the "Shoes" tree
    And I expand the "shoes" category
    And I click on the "vintage" category
    And I click on the "classy" category
    And I move on to the next step
    And I wait for the "update_product_value" job to finish
    And I am on the products page
    And I select the "Shoes" tree
    Then I should see the text "2014 collection (0)"
    Then I should see the text "Vintage (2)"
    And I should see the text "Classy (2)"

