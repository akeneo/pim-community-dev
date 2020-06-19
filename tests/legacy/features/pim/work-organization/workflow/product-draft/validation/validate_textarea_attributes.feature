@javascript
Feature: Validate textarea attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for textarea attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code             | label-en_US     | type                 | scopable | max_characters | wysiwyg_enabled | group     |
      | info             | Info            | pim_catalog_textarea | 0        | 5              | 0               | info      |
      | old_description  | Description     | pim_catalog_textarea | 1        | 5              | 0               | marketing |
      | long_info        | Longinfo        | pim_catalog_textarea | 0        | 20             | 1               | info      |
      | long_description | Longdescription | pim_catalog_textarea | 1        | 20             | 1               | marketing |
    And the following family:
      | code | label-en_US | attributes                                          |
      | baz  | Baz         | sku,info,long_info,old_description,long_description |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max characters constraint of textarea attribute
    Given I change the Info to "information"
    And I save the product
    Then I should see validation error "The info attribute must not contain more than 5 characters. The submitted value is too long."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable textarea attribute
    Given I visit the "Marketing" group
    And I change the Description for scope mobile to "information"
    And I save the product
    Then I should see validation error "The old_description attribute must not contain more than 5 characters. The submitted value is too long."
    And there should be 1 error in the "Marketing" tab

  Scenario: Validate the max characters constraint of textarea attribute with WYSIWYG
    Given I change the Longinfo to "the amazing information"
    And I save the product
    Then I should see validation error "The long_info attribute must not contain more than 20 characters. The submitted value is too long."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable textarea attribute with WYSIWYG
    Given I visit the "Marketing" group
    And I change the Longdescription for scope mobile to "the amazing information"
    And I save the product
    Then I should see validation error "The long_description attribute must not contain more than 20 characters. The submitted value is too long."
    And there should be 1 error in the "Marketing" tab
