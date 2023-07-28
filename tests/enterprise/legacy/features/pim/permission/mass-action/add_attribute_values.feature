@javascript @permission-feature-enabled
Feature: Mass add product value to products at once via a form
  In order to easily add value to products
  As a product manager
  I need to be able to add product values to many products at once via a form without erasing existing values


  @critical
  Scenario: It skips product if I can't edit them
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Mary"
    And I edit the "master_accessories_hats" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I submit the category changes
    And I edit the "supplier_abibas" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I submit the category changes
    And I edit the "print_accessories" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I submit the category changes
    And I edit the "master_accessories_scarves" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Redactor |
      | Allowed to edit products | Redactor |
      | Allowed to own products  | Redactor |
    And I submit the category changes
    When I am on the products grid
    And I select rows Hat and Scarf
    And I press the "Bulk actions" button
    And I choose the "Add attribute values" operation
    And I display the Collection attribute
    And I change the "Collection" to "Autumn 2016, Spring 2015"
    And I confirm mass edit
    And I wait for the "add_attribute_value" job to finish
    Then the options "collection" of products 1111111292 should be:
      | value       |
      | autumn_2016 |
      | spring_2015 |
    But the product "1111111240" should have the following values:
      | collection |  |
    When I go on the last executed job resume of "add_attribute_value"
    Then I should see the text "skipped products 1"
