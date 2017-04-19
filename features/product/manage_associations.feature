@javascript
Feature: Manage associations
  In order to be able to add and remove associations
  As a product manager
  I need to be able to add and remove associations

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
    | code  | label-en_US | type    |
    | CROSS | Bag Cross   | RELATED |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-6146
  Scenario: Successfully re-import product with deleted association
    Given the following CSV file to import:
    """
    sku;family;groups;categories;X_SELL-groups;X_SELL-products;name-en_US;description-en_US-tablet
    product_with_one_association;boots;CROSS;winter_boots;CROSS;deletable_product;Product with 1 association;
    deletable_product;sneakers;;winter_boots;;;Second Product;
    """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I should see the text "Association import COMPLETED"
    And I edit the "deletable_product" product
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I am on the "csv_footwear_product_import" import job page
    When I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Association import COMPLETED"

  @jira https://akeneo.atlassian.net/browse/PIM-6146
  Scenario Outline: Successfully remove deleted products linked to associations
    Given the following CSV file to import:
    """
    sku;family;groups;categories;X_SELL-groups;X_SELL-products;UPSELL-products;name-en_US
    product_with_one_association;boots;CROSS;winter_boots;CROSS;deletable_product;;Product with 1 association
    product_with_two_associations;boots;CROSS;winter_boots;CROSS;deletable_product,product_with_one_association;;Product with 2 associations
    product_with_two_associations_reverse;boots;CROSS;winter_boots;CROSS;product_with_one_association,deletable_product;;Product with 2 associations (reverse)
    product_with_two_associations_multi;boots;CROSS;winter_boots;CROSS;deletable_product,product_with_one_association;deletable_product,product_with_one_association;Product with 2 associations (several groups)
    product_without_association;boots;CROSS;winter_boots;CROSS;;;Product without association
    deletable_product;sneakers;;winter_boots;;;;Product to delete
    """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I should see the text "Association import COMPLETED"
    And I edit the "deletable_product" product
    And I press the secondary action "Delete"
    And I confirm the deletion
    When I edit the "<product_sku>" product
    And I visit the "Associations" tab
    And I visit the "<group_name>" group
    Then the rows "<checked_rows>" should be checked

  Examples:
    | product_sku                           | group_name | checked_rows                 |
    | product_with_one_association          | Cross sell |                              |
    | product_with_two_associations         | Cross sell | product_with_one_association |
    | product_with_two_associations_reverse | Cross sell | product_with_one_association |
    | product_with_two_associations_multi   | Cross sell | product_with_one_association |
    | product_with_two_associations_multi   | Upsell     | product_with_one_association |
    | product_without_association           | Cross sell |                              |
