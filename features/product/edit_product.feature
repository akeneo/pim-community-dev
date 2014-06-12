@javascript
Feature: Edit a product I have access
  In order to enrich the catalog
  As a product manager
  I need to be able edit and save a product I have access

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following categories:
      | code         | label-en_US   | parent    |
      | shoes        | Shoes         |           |
      | vintage      | Vintage       | shoes     |
      | trendy       | Trendy        | shoes     |
      | classy       | Classy        | shoes     |
      | boots        | Boots         |           |
    And the following category accesses:
      | category        | role          | access |
      | 2014_collection | Manager       |        |
      | shoes           | Manager       | edit   |
      | boots           | Administrator | view   |
    And the following products:
      | sku     | categories      | name-en |
      | rangers | vintage, classy | rangers |
      | boots   | boots           | boots   |

  #TODO: remove the skip tag when product ownership has been done
  @skip
  Scenario: Successfully create, edit and save a product I have access
    Given I am on the "rangers" product page
    And I fill in the following information:
      | Name | My Rangers |
    When I press the "Save" button
    Then I should be on the product "rangers" edit page
    Then the product Name should be "My Rangers"

  Scenario: Not being able to edit a product I have not access
    Given I am on the products page
    Then I should not be able to access the "boots" product page
