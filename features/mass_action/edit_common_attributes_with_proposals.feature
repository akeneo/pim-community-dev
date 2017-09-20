@javascript
Feature: Apply permissions for an attribute group when mass edit common attributes
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to create proposals on product I don't own

  Background:
    Given the "clothing" catalog configuration
    And the following family:
      | code       | attributes |
      | high_heels | sku,name   |
    And the following category:
      | code | label-en_US | parent          |
      | hat  | Hat         | 2014_collection |
    And the following product category accesses:
      | product category | user group | access |
      | hat              | Redactor   | own    |
      | tees             | Redactor   | edit   |
      | pants            | Redactor   | view   |
      | hat              | Manager    | own    |
      | tees             | Manager    | own    |
      | pants            | Manager    | own    |
    And the following products:
      | sku          | categories | family     |
      | owned        | hat        | high_heels |
      | editable     | tees       | high_heels |
      | viewable     | pants      | high_heels |
      | notviewable  | jeans      | high_heels |
      | unclassified |            | high_heels |

  @jira https://akeneo.atlassian.net/browse/PIM-3980 https://akeneo.atlassian.net/browse/PIM-4775
  Scenario: Successfully creates proposal on editable products
    Given I am logged in as "Mary"
    And I am on the products grid
    When I select rows viewable, editable and owned
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I display the Name attribute
    And I change the "Name" to "My awesome name"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "Proposal created 1"
    And I should see the text "skipped products 1"
    And I should see the text "processed 1"
    When I logout
    And I am logged in as "Julia"
    And I edit the "viewable" product
    And I visit the "Proposals" column tab
    Then the grid should contain 0 elements
    When I edit the "editable" product
    And I visit the "Proposals" column tab
    Then I should see the text "A draft is in progress by Mary for this product"
    And the grid should contain 1 elements
    When I edit the "owned" product
    And I visit the "Proposals" column tab
    Then the grid should contain 0 elements
