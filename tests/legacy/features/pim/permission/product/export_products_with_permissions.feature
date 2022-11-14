@javascript
Feature: Export products according to the granted permissions
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to the granted permissions

  @critical
  Scenario: Successfully apply permission on product export with the UI
    Given an "apparel" catalog configuration
    And the following job "tablet_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/tablet_product_export/tablet_product_export.csv"} |
      | filters   | {"structure":{"locales":["en_US"],"scope":"tablet","attributes":["sku"]}, "data": []}   |
      | with_uuid | yes                                                                                     |
    And the following categories:
      | code                | label-en_US         | parent   |
      | men_2015_restricted | Men 2015 restricted | men_2015 |
    And the following product category accesses:
      | product category    | user group | access |
      | men_2015_restricted | Manager    | view   |
    And the following products:
      | uuid                                 | sku                              | family  | categories          |
      | 59657be9-b9b2-408b-bcce-91700c9addc2 | product-not-viewable-by-redactor | tshirts | men_2015_restricted |
      | a72de069-dbc5-4439-83e9-b1636ebe0fa5 | product-viewable-by-redactor     | tshirts | men_2015            |
    And I am logged in as "Mary"
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    uuid;sku;categories;enabled;family;groups
    a72de069-dbc5-4439-83e9-b1636ebe0fa5;product-viewable-by-redactor;men_2015;1;tshirts;
    """
