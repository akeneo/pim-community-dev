@javascript
Feature: Classify a product in the trees I have access
  In order to classify products
  As a product manager
  I need to associate a product to categories I have access

  Background:
    Given the "clothing" catalog configuration
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
    And the following product category accesses:
      | product category | user group | access |
      | boots            | Manager    | view   |
      | shoes            | Manager    | view   |
      | vintage          | Manager    | view   |
      | trendy           | Manager    | view   |
      | classy           | Manager    | view   |
    And I am logged in as "Julia"

  @skip @critical
  Scenario: Associate a product to categories
    Given I edit the "rangers" product
    When I visit the "Categories" column tab
    And I select the "Shoes" tree
    And I expand the "shoes" category
    And I click on the "vintage" category
    And I click on the "classy" category
    And I press the "Save" button
    Then I should see the text "Shoes (2)"
    And I should not see the text "Slippers"

  @critical @jira https://akeneo.atlassian.net/browse/PIM-5402
  Scenario: Display only granted categories in the PEF
    Given the following product category accesses:
      | product category | user group | access |
      | slippers         | Manager    | none   |
    And I edit the "rangers" product
    When I visit the "Categories" column tab
    Then I should see the text "Boots"
    And I should see the text "Shoes"
    But I should not see the text "Slippers"
