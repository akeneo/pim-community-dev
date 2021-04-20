@javascript
Feature: Apply permissions for an attribute group when mass edit common attributes
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to create proposals on product I don't own

  Background:
    Given the "clothing" catalog configuration
    And the following family:
      | code       | attributes    |
      | high_heels | sku,name,size |
    And the following family variants:
      | code            | family     | variant-axes_1 | variant-attributes_1 |
      | high_heels_size | high_heels | size           | sku,size             |
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
      | sku                  | categories | family     |
      | product_owned        | hat        | high_heels |
      | product_editable     | tees       | high_heels |
      | product_viewable     | pants      | high_heels |
      | product_notviewable  | jeans      | high_heels |
      | product_unclassified |            | high_heels |
    And the following root product models:
      | code                       | parent | family_variant  | categories |
      | product_model_owned        |        | high_heels_size | hat        |
      | product_model_editable     |        | high_heels_size | tees       |
      | product_model_viewable     |        | high_heels_size | pants      |
      | product_model_notviewable  |        | high_heels_size | jeans      |
      | product_model_unclassified |        | high_heels_size |            |

  @jira https://akeneo.atlassian.net/browse/PIM-3980 https://akeneo.atlassian.net/browse/PIM-4775
  Scenario: Successfully creates proposal on editable products
    Given I am logged in as "Mary"
    And I am on the products grid
    When I select rows product_viewable, product_editable, product_owned, product_model_viewable, product_model_editable and product_model_owned
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I display the Name attribute
    And I change the "Name" to "My awesome name"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "Proposal created 1"
    And I should see the text "skipped products 2"
    And I should see the text "processed 3"
    When I logout
    And I am logged in as "Julia"
    And I edit the "product_viewable" product
    And I visit the "Proposals" column tab
    Then the grid should contain 0 elements
    When I edit the "product_editable" product
    And I visit the "Proposals" column tab
    Then I should see the text "A draft is in progress by Mary Smith for this product."
    And the grid should contain 1 elements
    When I edit the "product_owned" product
    And I visit the "Proposals" column tab
    Then the grid should contain 0 elements
