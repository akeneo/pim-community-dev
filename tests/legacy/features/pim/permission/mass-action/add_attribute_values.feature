@javascript
Feature: Mass add product value to products at once via a form
  In order to easily add value to products
  As a product manager
  I need to be able to add product values to many products at once via a form without erasing existing values

  @critical
  Scenario: It skips product if I can't edit them
    Given a "catalog_modeling" catalog configuration
    Given I am logged in as "Mary"
    And I edit the "master_accessories_hats" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I save the category
    And I edit the "supplier_abibas" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I save the category
    And I edit the "print_accessories" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I save the category
    And I edit the "master_accessories_scarves" category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products | Redactor |
      | Allowed to own products  | Redactor |
    And I save the category
    When I am on the products grid
    And I select rows Hat and Scarf
    And I press the "Bulk actions" button
    And I choose the "Add attributes values" operation
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

  Scenario: I can mass add assets to products
    Given a "default" catalog configuration
    And the following assets category:
      | code               | parent | label-en_US |
      | asset_main_catalog |        | Main        |
    And the following attributes:
      | label-en_US | group | type                  | code   | reference_data_name | localizable | scopable |
      | Assets      | other | pim_assets_collection | assets | assets              | 0           | 0        |
    And the following family:
      | code    | attributes |
      | megazor | assets     |
    And the following assets:
      | code    | categories         |
      | video_1 | asset_main_catalog |
      | video_2 | asset_main_catalog |
      | video_3 | asset_main_catalog |
    And the following products:
      | sku         | family  | assets           |
      | super_watch | megazor | video_1, video_2 |
      | super_hat   | megazor |                  |
    And I am logged in as "Julia"
    When I am on the products grid
    And I filter by "family" with operator "in list" and value "megazor"
    And I select rows super_watch and super_hat
    And I press the "Bulk actions" button
    And I choose the "Add attributes values" operation
    And I display the Assets attribute
    And I start to manage assets for "Assets"
    And I check the row "video_3"
    And I confirm the asset modification
    When I confirm mass edit
    And I wait for the "add_attribute_value" job to finish
    Then the product "super_watch" should have the following values:
      | assets | [video_3], [video_1], [video_2] |
    And the product "super_hat" should have the following values:
      | assets | [video_3] |
