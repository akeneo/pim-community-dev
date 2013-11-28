@javascript
Feature: Import attributes
  In order to reuse the attributes of my products
  As Julia
  I need to be able to import attributes

  Scenario: Successfully import attributes
    Given the "default" catalog configuration
    And the following attribute group:
      | code      | label     |
      | info      | Info      |
      | marketing | Marketing |
      | media     | Media     |
      | sizes     | Sizes     |
      | colors    | Colors    |
    And the following jobs:
      | connector            | alias            | code                  | label                     | type   |
      | Akeneo CSV Connector | attribute_import | acme_attribute_import | Attribute import for Acme | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_column;useable_as_grid_filter;is_translatable;is_scopable;allowed_extensions;date_type;metric_family;default_metric_unit
    pim_catalog_text;name;Name;info;0;1;1;1;0;;;;
    pim_catalog_simpleselect;manufacturer;Manufacturer;info;0;1;1;0;0;;;;
    pim_catalog_multiselect;weather_conditions;"Weather conditions";info;0;1;1;0;0;;;;
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
      | step   | element | property | value                |
      | import | reader  | filePath | {{ file to import }} |
    When I am on the "acme_attribute_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following attributes:
      | type                         | code               | label-en_US        | group     | unique | useable_as_grid_column | useable_as_grid_filter | is_translatable | is_scopable | allowed_extensions | date_type | metric_family | default_metric_unit |
      | pim_catalog_text             | name               | Name               | info      | 0      | 1                      | 1                      | 1               | 0           |                    |           |               |                     |
      | pim_catalog_simpleselect     | manufacturer       | Manufacturer       | info      | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_multiselect      | weather_conditions | Weather conditions | info      | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_textarea         | description        | Description        | info      | 0      | 0                      | 1                      | 1               | 1           |                    |           |               |                     |
      | pim_catalog_price_collection | price              | Price              | marketing | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_simpleselect     | rating             | Rating             | marketing | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_simpleselect     | size               | Size               | sizes     | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_simpleselect     | color              | Color              | colors    | 0      | 1                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_simpleselect     | lace_color         | Lace color         | colors    | 0      | 0                      | 1                      | 0               | 0           |                    |           |               |                     |
      | pim_catalog_image            | image_upload       | Image upload       | media     | 0      | 0                      | 0                      | 0               | 0           | gif,png            |           |               |                     |
      | pim_catalog_date             | release            | Release date       | info      | 0      | 1                      | 1                      | 0               | 0           |                    | datetime  |               |                     |
      | pim_catalog_metric           | length             | Length             | info      | 0      | 0                      | 0                      | 0               | 0           |                    |           | Length        | CENTIMETER          |

