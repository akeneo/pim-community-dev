@javascript
Feature: Validate textarea attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for textarea attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code             | label-en_US     | type     | scopable | max_characters | wysiwyg_enabled |
      | info             | Info            | textarea | no       | 5              | no              |
      | description      | Description     | textarea | yes      | 5              | no              |
      | long_info        | Longinfo        | textarea | no       | 10             | yes             |
      | long_description | Longdescription | textarea | yes      | 10             | yes             |
    And the following family:
      | code | label-en_US | attributes                                          |
      | baz  | Baz         | sku, info, long_info, description, long_description |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max characters constraint of textarea attribute
    Given I change the Info to "information"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 5 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the max characters constraint of scopable textarea attribute
    Given I change the "ecommerce Description" to "information"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 5 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the max characters constraint of textarea attribute with WYSIWYG
    Given I change the "Longinfo" to "information"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 10 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the max characters constraint of scopable textarea attribute with WYSIWYG
    Given I change the "ecommerce Longdescription" to "information"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 10 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red
