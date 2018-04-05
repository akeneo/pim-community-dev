@javascript
Feature: Mass edit many products at once via a form
  In order to easily organize many products
  As a product manager
  I need to be able to mass edit a selection of products greater than the batch size

  Scenario: Add products to a category
    Given the "default" catalog configuration
    And 103 empty products
    And the following category:
      | code            | parent  | label-en_US     |
      | 2018_collection | default | 2018 collection |
    And I am logged in as "Julia"
    And I am on the products grid
    And I sort by "ID" value ascending
    And I select rows "product_1"
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I expand the "default" category
    And I press the "2018 collection" button
    And I confirm mass edit
    And I wait for the "add_product_value" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "read 103"
    And I should see the text "processed 103"
    And the category of the product "product_1" should be "2018_collection"

  @jira https://akeneo.atlassian.net/browse/PIM-7203
  Scenario: Edit an attribute on several products
    Given the "default" catalog configuration
    And the following attributes:
      | code    | label-en_US | type              | group |
      | name    | Name        | pim_catalog_text  | other |
    And the following family:
      | code     | attributes  | attribute_as_label |
      | flipflop | name        | name               |
    And 103 empty products for the "flipflop" family
    And I am logged in as "Julia"
    And I am on the products grid
    When I sort by "ID" value ascending
    And I select rows "product_1"
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Edit attributes" operation
    And I move on to the choose step
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "Same product"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "read 103"
    And I should see the text "processed 103"
