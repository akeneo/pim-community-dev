@javascript
Feature: Export products according to text attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to text attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code       | type             | label-en_US    | group |
      | title      | pim_catalog_text | Title          | other |
      | title_2    | pim_catalog_text | Title 2        | other |
      | title_3    | pim_catalog_text | Title 3        | other |
    And the following family:
      | code    | requirements-mobile | attributes                         |
      | rangers | sku                 | sku,comment,name,title,title_2,title_3 |
    And the following products:
      | uuid                                 | sku      | enabled | family  | categories        | comment         | name-en_US | title        | title_2       | title_3    |
      | ad087739-9bd9-4ee5-a8a9-378864c67a26 | SNKRS-1B | 1       | rangers | summer_collection | Awesome         | Ranger 1B  | My title     | Awesome title |            |
      | f601bebb-8678-4db0-a56e-31bcedbb9153 | SNKRS-1R | 1       | rangers | summer_collection | Awesome product |            | Nice product |               |            |
      | f12b2144-a725-459c-b134-efd349719d97 | SNKRS-1N | 1       | rangers | summer_collection |                 |            |              | Amazing title |            |
      | d06d9eb9-7df9-4316-a88c-e2c708b54267 | SNKRS-1Z | 1       | rangers | summer_collection | This is nice    | Ranger 1Z  |              |               | Nice title |
    And the following job "csv_footwear_product_export" configuration:
      | with_uuid | yes |
    And I am logged in as "Julia"

  Scenario: Export products by text values
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I visit the "Content" tab
    And I add available attributes Comment
    And I add available attributes Title
    And I add available attributes Title 2
    And I add available attributes Title 3
    And I add available attributes Name
    And I switch the locale from "name" filter to "en_US"
    And I filter by "comment" with operator "Is equal to" and value "Awesome"
    And I filter by "name" with operator "Contains" and value "Ranger"
    And I filter by "title" with operator "Does not contain" and value "product"
    And I filter by "title_2" with operator "Starts with" and value "Awesome"
    And I filter by "title_3" with operator "Is empty" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    uuid;sku;categories;enabled;family;groups;comment;name-en_US;title;title_2;title_3
    ad087739-9bd9-4ee5-a8a9-378864c67a26;SNKRS-1B;summer_collection;1;rangers;;Awesome;"Ranger 1B";"My title";"Awesome title";
    """
