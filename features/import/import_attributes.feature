@javascript
Feature: Import attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import attributes

  Scenario: Successfully import attributes
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_column;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
    pim_catalog_text;shortname;Shortname;info;0;1;1;1;0;;;
    pim_catalog_simpleselect;provider;Provider;info;0;1;1;0;0;;;
    pim_catalog_multiselect;season;"Season";info;0;1;1;0;0;;;
    pim_catalog_textarea;commentary;Commentary;info;0;0;1;1;1;;;
    pim_catalog_price_collection;public_price;"Public price";marketing;0;1;1;0;0;;;
    pim_catalog_simpleselect;grade;Grade;marketing;0;1;1;0;0;;;
    pim_catalog_simpleselect;width;Width;sizes;0;1;1;0;0;;;
    pim_catalog_simpleselect;hue;Hue;colors;0;1;1;0;0;;;
    pim_catalog_simpleselect;sole_color;"Sole color";colors;0;0;1;0;0;;;
    pim_catalog_image;image_upload;"Image upload";media;0;0;0;0;0;gif,png;;
    pim_catalog_date;release;"Release date";info;0;1;1;0;0;;;
    pim_catalog_metric;lace_length;"Lace length";info;0;0;0;0;0;;Length;CENTIMETER

    """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then there should be the following attributes:
      | type         | code         | label-en_US  | group     | unique | useable_as_grid_column | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit |
      | text         | shortname    | Shortname    | info      | 0      | 1                      | 1                      | 1           | 0        |                    |               |                     |
      | simpleselect | provider     | Provider     | info      | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | multiselect  | season       | Season       | info      | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | textarea     | commentary   | Commentary   | info      | 0      | 0                      | 1                      | 1           | 1        |                    |               |                     |
      | prices       | public_price | Public price | marketing | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | grade        | Grade        | marketing | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | width        | Width        | sizes     | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | hue          | Hue          | colors    | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | sole_color   | Sole color   | colors    | 0      | 0                      | 1                      | 0           | 0        |                    |               |                     |
      | image        | image_upload | Image upload | media     | 0      | 0                      | 0                      | 0           | 0        | gif,png            |               |                     |
      | date         | release      | Release date | info      | 0      | 1                      | 1                      | 0           | 0        |                    |               |                     |
      | metric       | lace_length  | Lace length  | info      | 0      | 0                      | 0                      | 0           | 0        |                    | Length        | CENTIMETER          |

  Scenario: Fail to change immutable properties of attributes during the import
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following CSV to import:
      | type                   | pim_catalog_date | pim_catalog_metric |
      | code                   | release_date     | weight             |
      | unique                 | no               | no                 |
      | useable_as_grid_column | yes              | yes                |
      | useable_as_grid_filter | yes              | yes                |
      | localizable            | yes              | no                 |
      | scopable               | no               | no                 |
      | metric_family          |                  | Length             |
      | default_metric_unit    |                  | METER              |
      | allowed_extensions     |                  |                    |
    And the following job "attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "attribute_import" import job page
    And I launch the import job
    And I wait for the "attribute_import" job to finish
    And I should see "metricFamily: This property cannot be changed."
