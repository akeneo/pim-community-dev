@javascript
Feature: Display the missing required attributes
  In order to ease the enrichment of products
  As a product manager
  I need to be able to display the missing required attributes

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: Display missing required attributes on product models
    Given I am logged in as "Julia"
    When I am on the "apollon" product model page
    Then I should see the text "1 missing required attribute"
    When I remove the "Model picture" file
    And I save the product model
    Then I should see the text "2 missing required attributes"
    When I am on the "apollon_pink" product model page
    Then I should see the text "5 missing required attributes"
    When I am on the "medias" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
      | Allowed to edit attributes | IT support |
    And I save the attribute group
    When I am on the "apollon_pink" product model page
    Then I should not see the text "5 missing required attributes"
    But I should see the text "3 missing required attributes"

  Scenario: Don't display missing required attribute if attribute is not editable because of permission on attribute group
    Given I am logged in as "Julia"
    When I am on the "product" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support, Manager |
      | Allowed to edit attributes | IT support          |
    And I save the attribute group
    When I am on the "medias" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support, Manager |
      | Allowed to edit attributes | IT support          |
    And I save the attribute group
    When I am on the "apollon_pink" product model page
    Then I should not see any missing required attribute

  Scenario: Don't display missing required attribute if attribute is not editable because of permission on local
    Given I am logged in as "Julia"
    When I am on the "apollon" product model page
    And I fill in the following information:
      | Model description |  |
    And I save the product model
    Given the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | view   |
      | en_US  | Redactor   | view   |
    When I refresh current page
    And I switch the locale to "en_US"
    Then the field Model description should be disabled
    And I should see the text "1 missing required attribute" in the total missing required attributes

  Scenario: Don't display missing required attribute if product is not editable because of categories
    Given I am logged in as "Mary"
    When I am on the "watch" product page
    Then I should see the text "4 missing required attributes" in the total missing required attributes
    And I should see the text "3 missing required attributes"
    When I edit the "supplier_zaro" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Manager, Redactor |
    And I save the category
    When I am on the "watch" product page
    Then I should see the text "Marketing"
    But I should not see any missing required attribute
    And I should not see the text "3 missing required attributes"
