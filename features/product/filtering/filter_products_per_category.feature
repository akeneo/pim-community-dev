@javascript
Feature: Filter products by category
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products by category

  # @jira https://akeneo.atlassian.net/browse/PIM-3308 (a product should be multi-categorized to handle all cases)
  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku           | categories             |
      | purple-tshirt | women_2015, women_2014 |
      | green-tshirt  | women_2015             |
      | akeneo-mug    |                        |
      | blue-jeans    | men_2015_summer        |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by category
    Given I am on the products page
    When I select the "2015 collection" tree
    Then I should see products purple-tshirt, green-tshirt and blue-jeans
    When I uncheck the "Include sub-categories" switch
    Then I should be able to use the following filters:
      | filter   | value        | result                         |
      | category | women_2015   | purple-tshirt and green-tshirt |
      | category | men_2015     |                                |
      | category | unclassified | akeneo-mug                     |
    When I check the "Include sub-categories" switch
    Then I should be able to use the following filters:
      | filter   | value    | result     |
      | category | men_2015 | blue-jeans |

  @jira https://akeneo.atlassian.net/browse/PIM-5726
  Scenario: Successfully filter products by category when hiding the category sidebar
    Given I am on the products page
    When I select the "2015 collection" tree
    Then I should see products purple-tshirt, green-tshirt and blue-jeans
    Then I should be able to use the following filters:
      | filter   | value      | result                         |
      | category | women_2015 | purple-tshirt and green-tshirt |
    And the grid should contain 2 elements
    When I collapse the category sidebar
    And I change the page size to 50
    Then the grid should contain 2 elements
    When I expand the category sidebar
    And I change the page size to 25
    Then the grid should contain 2 elements
