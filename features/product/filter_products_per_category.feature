@javascript
Feature: Filter products by category
  In order to enrich my catalog
  As a user
  I need to be able to manually filter products by category

  Background:
    Given the "default" catalog configuration
    And the following products:
      | sku           |
      | purple-tshirt |
      | green-tshirt  |
      | akeneo-mug    |
      | blue-jeans    |
    And the following categories:
      | code     | parent   | label-en_US | products                   |
      | catalog  |          | Catalog     |                            |
      | tshirts  | catalog  | TShirts     | purple-tshirt,green-tshirt |
      | trousers | catalog  | Trousers    |                            |
      | jeans    | trousers | Jeans       | blue-jeans                 |
    And I am logged in as "admin"

  Scenario: Successfully filter products by category
    Given I am on the products page
    When I select the "Catalog" tree
    Then I should see products akeneo-mug, purple-tshirt, green-tshirt and blue-jeans
    And I should be able to use the following filters:
      | filter   | value        | result                         |
      | category | tshirts      | purple-tshirt and green-tshirt |
      | category | trousers     |                                |
      | category | unclassified | akeneo-mug                     |
    When I check the "Include sub-categories" switch
    When I select the "Catalog" tree
    Then I should be able to use the following filters:
      | filter   | value    | result     |
      | category | trousers | blue-jeans |
