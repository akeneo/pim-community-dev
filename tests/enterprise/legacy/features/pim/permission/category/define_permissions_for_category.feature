@javascript @permission-feature-enabled
Feature: Define permissions for a category
  In order to be able to prevent some users from viewing some products
  As an administrator
  I need to be able to define permissions for categories

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "peter"

  @critical
  Scenario: Create category keeps the parent's permissions
    Given I am on the category tree "2014_collection" page
    And I hover over the category tree item "2014 collection"
    And I press the "New category" button
    And I create the category with code newcategory
    When I edit the "newcategory" category
    And I open the category tab "Permissions"
    Then I should see the category permission Allowed to view products with user groups IT support, Manager and Redactor
    And I should see the category permission Allowed to edit products with user groups IT support, Manager and Redactor
    And I should see the category permission Allowed to own products with user groups IT support and Manager

  @critical
  Scenario: By default, update children when the parent's permissions are changed
    Given the following categories:
      | code    | label-en_US | parent |
      | shoes   | Shoes       |        |
      | vintage | Vintage     | shoes  |
      | trendy  | Trendy      | shoes  |
      | classy  | Classy      | shoes  |
    When I edit the "shoes" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Manager |
    And I submit the category changes
    And I refresh current page
    Then I edit the "classy" category
    And I open the category tab "Permissions"
    And I should see the category permission Allowed to view products with user groups Manager

  # @jira https://akeneo.atlassian.net/browse/PIM-5999
  @critical
  Scenario: Revoke category access let the user lists categories
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I edit the "default" category
    And I open the category tab "Permissions"
    When I remove all the category permission from "Allowed to view products"
    And I submit the category changes
    And I go to the categories page
    Then I should see the text "Master catalog"
