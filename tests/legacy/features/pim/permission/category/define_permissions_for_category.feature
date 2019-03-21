@javascript
Feature: Define permissions for a category
  In order to be able to prevent some users from viewing some products
  As an administrator
  I need to be able to define permissions for categories

  @critical
  Scenario: Create category keeps the parent's permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"
    And I am on the category "2014_collection" node creation page
    And I fill in the following information:
      | Code | newcategory |
    And I save the category
    When I edit the "newcategory" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups IT support, Manager and Redactor
    And I should see the permission Allowed to edit products with user groups IT support, Manager and Redactor
    And I should see the permission Allowed to own products with user groups IT support and Manager

  @critical
  Scenario: By default, update children when the parent's permissions are changed
    Given a "clothing" catalog configuration
    And the following categories:
      | code    | label-en_US | parent |
      | shoes   | Shoes       |        |
      | vintage | Vintage     | shoes  |
      | trendy  | Trendy      | shoes  |
      | classy  | Classy      | shoes  |
    And I am logged in as "Peter"
    And I edit the "shoes" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Manager |
    And I save the category
    When I edit the "classy" category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view products with user groups Manager

  @critical @jira https://akeneo.atlassian.net/browse/PIM-5999
  Scenario: Revoke category access let the user lists categories
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    When I edit the "default" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | |
    And I save the category
    And I edit the "default" category
    Then I should see the text "Master catalog"
    But I should not see the text "No tree available"
