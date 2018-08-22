@javascipt
Feature: Browse products I have access to
  In order to enrich the products
  As a regular user
  I need to browse products I have access to

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku     |
      | rangers |
      | loafer  |
    And the following categories:
      | code     | label-en_US | parent |
      | shoes    | Shoes       |        |
      | vintage  | Vintage     | shoes  |
      | trendy   | Trendy      | shoes  |
      | classy   | Classy      | shoes  |
      | boots    | Boots       |        |
      | slippers | Slippers    |        |
    #TODO:
    And the following product category accesses:
      | product category | user group | access |
      | shoes            | User       | view   |
      | slippers         | User       | view   |
    And I am logged in as "Mary"

  @skip
  Scenario: Browse products
    When I am on the products grid
    And I select the "Shoes" tree
    Then I should see products rangers and loafer
    And I select the "Slippers" tree
