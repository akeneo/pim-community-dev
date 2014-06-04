@javascript
Feature: Classify a product in the trees I have access
  In order to classify products
  As Julia
  I need to associate a product to categories I have access

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
      | slippers     | Slippers      |           |
    #TODO: add this directly in the Behat data set
    And the following attribute group accesses:
      | attribute group | role          | access |
      | info            | Administrator | edit   |
    And the following category accesses:
      | category | role          | access |
      | shoes    | Administrator | view   |
      | slippers | Administrator | view   |
    And I am logged in as "Julia"

  Scenario: Associate a product to categories
    Given I edit the "rangers" product
    When I visit the "Categories" tab
    And I select the "Slippers" tree
    And I select the "Shoes" tree
    And I expand the "Shoes" category
    And I click on the "Vintage" category
    And I click on the "Classy" category
    And I press the "Save" button
    Then I should see "Shoes (2)"
