@javascript
Feature: Validate textarea attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for textarea attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code             | label-en_US     | type     | scopable | max_characters | wysiwyg_enabled | group      |
      | info             | Info            | textarea | no       | 5              | no              | info       |
      | old_description  | Description     | textarea | yes      | 5              | no              | marketing  |
      | long_info        | Longinfo        | textarea | no       | 10             | yes             | info       |
      | long_description | Longdescription | textarea | yes      | 10             | yes             | marketing  |
    And the following family:
      | code | label-en_US | attributes                                              |
      | baz  | Baz         | sku, info, long_info, old_description, long_description |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max characters constraint of textarea attribute
    Given I change the Info to "information"
    And I save the product
    Then I should see validation error "This value is too long. It should have 5 characters or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable textarea attribute
    Given I visit the "Marketing" group
    And I change the Description for scope mobile to "information"
    And I save the product
    Then I should see validation error "This value is too long. It should have 5 characters or less."
    And there should be 1 error in the "Marketing" tab

  Scenario: Validate the max characters constraint of textarea attribute with WYSIWYG
    Given I change the Longinfo to "information"
    And I save the product
    Then I should see validation error "This value is too long. It should have 10 characters or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of scopable textarea attribute with WYSIWYG
    Given I visit the "Marketing" group
    And I change the Longdescription for scope mobile to "information"
    And I save the product
    Then I should see validation error "This value is too long. It should have 10 characters or less."
    And there should be 1 error in the "Marketing" tab
