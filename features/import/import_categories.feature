@javascript
Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Set parent's permissions to new categories
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;parent;label-en_US
    autumn_collection;2014_collection;Autumn collection
    black_tshirts;tshirts; Black tshirts
    """
    And the following job "clothing_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_category_import" import job page
    And I launch the import job
    And I wait for the "clothing_category_import" job to finish
    Then there should be the following categories:
      | code              | label             | parent            |
      | 2014_collection   | 2014 collection   |                   |
      | autumn_collection | Autumn collection | 2014_collection   |
      | tshirts           | Tshirts           | summer_collection |
      | black_tshirts     | Black tshirts     | tshirts           |
    When I edit the "autumn_collection" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups IT support, Manager and Redactor
    And I should see the permission Allowed to edit products with user groups IT support, Manager and Redactor
    And I should see the permission Allowed to own products with user groups IT support and Manager
    When I edit the "black_tshirts" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups IT support and Manager
    And I should see the permission Allowed to edit products with user groups IT support
    And I should see the permission Allowed to own products with user groups IT support


  Scenario: Set default permissions to categories that belongs to a new tree
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;parent;label-en_US
    2015_collection;;2015 collection
    2015_jeans;2015_collection;2015 jeans
    2015_tees;2015_collection;2015 tees
    """
    And the following job "clothing_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_category_import" import job page
    And I launch the import job
    And I wait for the "clothing_category_import" job to finish
    Then there should be the following categories:
      | code            | label           | parent          |
      | 2015_collection | 2015 collection |                 |
      | 2015_jeans      | 2015 jeans      | 2015_collection |
      | 2015_tees       | 2015 tees       | 2015_collection |
    When I edit the "2015_collection" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups All
    And I should see the permission Allowed to edit products with user groups All
    And I should see the permission Allowed to own products with user groups All
    When I edit the "2015_jeans" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups All
    And I should see the permission Allowed to edit products with user groups All
    And I should see the permission Allowed to own products with user groups All
    When I edit the "2015_tees" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups All
    And I should see the permission Allowed to edit products with user groups All
    And I should see the permission Allowed to own products with user groups All



