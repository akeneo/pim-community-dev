Feature: Import attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import attributes

  Scenario: Successfully import attributes in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;sort_order;decimals_allowed;negative_allowed
      pim_catalog_text;shortname;Shortname;info;0;1;1;0;;;;1;;
      pim_catalog_simpleselect;provider;Provider;info;0;1;0;0;;;;4;;
      pim_catalog_multiselect;season;"Season";info;0;1;0;0;;;;2;;
      pim_catalog_textarea;commentary;Commentary;info;0;1;1;1;;;;7;;
      pim_catalog_price_collection;public_price;"Public price";marketing;0;1;0;0;;;;0;0;
      pim_catalog_simpleselect;grade;Grade;marketing;0;1;0;0;;;;0;;
      pim_catalog_simpleselect;width;Width;sizes;0;1;0;0;;;;3;;
      pim_catalog_simpleselect;hue;Hue;colors;0;1;0;0;;;;13;;
      pim_catalog_simpleselect;buckle_color;"Buckle color";colors;0;1;0;0;;;;0;;
      pim_catalog_image;image_upload;"Image upload";media;0;0;0;0;gif,png;;;0;;
      pim_catalog_date;release;"Release date";info;0;1;0;0;;;;0;;
      pim_catalog_metric;lace_length;"Lace length";info;0;0;0;0;;Length;CENTIMETER;0;0;0
      """
    When the attributes are imported via the job csv_footwear_attribute_import
    Then there should be the following attributes:
      | type                         | code         | label-en_US  | group     | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | sort_order |
      | pim_catalog_text             | shortname    | Shortname    | info      | 0      | 1                      | 1           | 0        |                    |               |                     | 1          |
      | pim_catalog_simpleselect     | provider     | Provider     | info      | 0      | 1                      | 0           | 0        |                    |               |                     | 4          |
      | pim_catalog_multiselect      | season       | Season       | info      | 0      | 1                      | 0           | 0        |                    |               |                     | 2          |
      | pim_catalog_textarea         | commentary   | Commentary   | info      | 0      | 1                      | 1           | 1        |                    |               |                     | 7          |
      | pim_catalog_price_collection | public_price | Public price | marketing | 0      | 1                      | 0           | 0        |                    |               |                     | 0          |
      | pim_catalog_simpleselect     | grade        | Grade        | marketing | 0      | 1                      | 0           | 0        |                    |               |                     | 0          |
      | pim_catalog_simpleselect     | width        | Width        | sizes     | 0      | 1                      | 0           | 0        |                    |               |                     | 3          |
      | pim_catalog_simpleselect     | hue          | Hue          | colors    | 0      | 1                      | 0           | 0        |                    |               |                     | 13         |
      | pim_catalog_simpleselect     | buckle_color | Buckle color | colors    | 0      | 1                      | 0           | 0        |                    |               |                     | 0          |
      | pim_catalog_image            | image_upload | Image upload | media     | 0      | 0                      | 0           | 0        | gif,png            |               |                     | 0          |
      | pim_catalog_date             | release      | Release date | info      | 0      | 1                      | 0           | 0        |                    |               |                     | 0          |
      | pim_catalog_metric           | lace_length  | Lace length  | info      | 0      | 0                      | 0           | 0        |                    | Length        | CENTIMETER          | 0          |

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  @javascript
  Scenario: Skip new attributes with invalid data during an import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;sort_order
      pim_catalog_simpleselect;lace_color;"New lace color";colors;0;1;0;0;;;;0
      pim_catalog_metric;new_length;"New length";info;0;0;0;0;;Length;INVALID_LENGTH;0
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see the text "skipped 1"
    And there should be the following attributes:
      | type                     | code       | label-en_US    | group  | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | sort_order |
      | pim_catalog_simpleselect | lace_color | New lace color | colors | 0      | 1                      | 0           | 0        |                    |               |                     | 0          |
    And there should be 27 attributes

  @critical @javascript
  Scenario: Successfully import and update existing attribute
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;localizable;scopable;available_locales;sort_order
      pim_catalog_simpleselect;manufacturer;Meine große Code;My awesome code;Mon super code;marketing;0;1;0;0;en_US,fr_FR;3
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see the text "read lines 1"
    Then I should see the text "processed 1"
    And there should be the following attributes:
      | type                     | code         | label-en_US     | label-de_DE      | label-fr_FR    | group     | unique | useable_as_grid_filter | localizable | scopable | localizable | scopable | available_locales | sort_order |
      | pim_catalog_simpleselect | manufacturer | My awesome code | Meine große Code | Mon super code | marketing | 0      | 1                      | 0           | 0        | 0           | 0        | en_US,fr_FR       | 3          |
