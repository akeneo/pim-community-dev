@javascript
Feature: Classify many products at once for the tree I have access
  In order to easily classify products
  As Julia
  I need to associate many products to categories I have access at once

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku     |
      | rangers |
      | loafer  |
    And the following categories:
      | code         | label-en_US   | parent    |
      | shoes        | Shoes         |           |
      | vintage      | Vintage       | shoes     |
      | trendy       | Trendy        | shoes     |
      | classy       | Classy        | shoes     |
      | boots        | Boots         |           |
    #TODO: add this directly in the Behat data set
    And the following attribute group accesses:
      | attribute group | role          | access |
      | info            | Administrator | edit   |
    And the following category accesses:
      | category | role          | access |
      | shoes    | Administrator | view   |
    And I am logged in as "Julia"
    And I am on the products page

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
    And I am on the products page
    And I select the "Shoes" tree
    Then I should see "Vintage (2)"
    And I should see "Classy (2)"

