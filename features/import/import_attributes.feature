@javascript
Feature: Import attributes
  In order to reuse the attributes of my products
  As Julia
  I need to be able to import attributes

  Scenario: Successfully import attributes
    Given the "default" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | info      | Info        |
      | marketing | Marketing   |
      | media     | Media       |
      | sizes     | Sizes       |
      | colors    | Colors      |
    And the following job:
      | connector            | alias                | code                  | label                     | type   |
      | Akeneo CSV Connector | csv_attribute_import | acme_attribute_import | Attribute import for Acme | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_column;useable_as_grid_filter;translatable;scopable;allowed_extensions;date_type;metric_family;default_metric_unit
    pim_catalog_text;name;Name;info;0;1;1;1;0;;;;
    pim_catalog_simpleselect;manufacturer;Manufacturer;info;0;1;1;0;0;;;;
    pim_catalog_multiselect;weather_cond;"Weather conditions";info;0;1;1;0;0;;;;
    pim_catalog_textarea;description;Description;info;0;0;1;1;1;;;;
    pim_catalog_price_collection;price;Price;marketing;0;1;1;0;0;;;;
    pim_catalog_simpleselect;rating;Rating;marketing;0;1;1;0;0;;;;
    pim_catalog_simpleselect;size;Size;sizes;0;1;1;0;0;;;;
    pim_catalog_simpleselect;color;Color;colors;0;1;1;0;0;;;;
    pim_catalog_simpleselect;lace_color;"Lace color";colors;0;0;1;0;0;;;;
    pim_catalog_image;image_upload;"Image upload";media;0;0;0;0;0;gif,png;;;
    pim_catalog_date;release;"Release date";info;0;1;1;0;0;;datetime;;
    pim_catalog_metric;length;Length;info;0;0;0;0;0;;;Length;CENTIMETER

    """
    And the following job "acme_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "acme_attribute_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following attributes:
      | type         | code         | label-en_US        | group     | unique | useable_as_grid_column | useable_as_grid_filter | translatable | scopable | allowed_extensions | date_type | metric_family | default_metric_unit |
      | text         | name         | Name               | info      | 0      | 1                      | 1                      | 1            | 0        |                    |           |               |                     |
      | simpleselect | manufacturer | Manufacturer       | info      | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | multiselect  | weather_cond | Weather conditions | info      | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | textarea     | description  | Description        | info      | 0      | 0                      | 1                      | 1            | 1        |                    |           |               |                     |
      | prices       | price        | Price              | marketing | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | simpleselect | rating       | Rating             | marketing | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | simpleselect | size         | Size               | sizes     | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | simpleselect | color        | Color              | colors    | 0      | 1                      | 1                      | 0            | 0        |                    |           |               |                     |
      | simpleselect | lace_color   | Lace color         | colors    | 0      | 0                      | 1                      | 0            | 0        |                    |           |               |                     |
      | image        | image_upload | Image upload       | media     | 0      | 0                      | 0                      | 0            | 0        | gif,png            |           |               |                     |
      | date         | release      | Release date       | info      | 0      | 1                      | 1                      | 0            | 0        |                    | datetime  |               |                     |
      | metric       | length       | Length             | info      | 0      | 0                      | 0                      | 0            | 0        |                    |           | Length        | CENTIMETER          |

  Scenario: Fail to change immutable properties of attributes during the import
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following CSV to import:
      | type                   | pim_catalog_date | pim_catalog_metric |
      | code                   | release_date     | weight             |
      | unique                 | no               | no                 |
      | useable_as_grid_column | yes              | yes                |
      | useable_as_grid_filter | yes              | yes                |
      | translatable           | yes              | no                 |
      | scopable               | no               | no                 |
      | date_type              | time             |                    |
      | metric_family          |                  | Length             |
      | default_metric_unit    |                  | METER              |
      | allowed_extensions     |                  |                    |
    And the following job "attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "attribute_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then I should see "dateType: This property may not be changed."
    And I should see "metricFamily: This property may not be changed."
