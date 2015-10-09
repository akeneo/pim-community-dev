@javascript
Feature: Import attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import attributes

  Scenario: Successfully import attributes
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
      pim_catalog_text;shortname;Shortname;info;0;1;1;0;;;
      pim_catalog_simpleselect;provider;Provider;info;0;1;0;0;;;
      pim_catalog_multiselect;season;"Season";info;0;1;0;0;;;
      pim_catalog_textarea;commentary;Commentary;info;0;1;1;1;;;
      pim_catalog_price_collection;public_price;"Public price";marketing;0;1;0;0;;;
      pim_catalog_simpleselect;grade;Grade;marketing;0;1;0;0;;;
      pim_catalog_simpleselect;width;Width;sizes;0;1;0;0;;;
      pim_catalog_simpleselect;hue;Hue;colors;0;1;0;0;;;
      pim_catalog_simpleselect;buckle_color;"Buckle color";colors;0;1;0;0;;;
      pim_catalog_image;image_upload;"Image upload";media;0;0;0;0;gif,png;;
      pim_catalog_date;release;"Release date";info;0;1;0;0;;;
      pim_catalog_metric;lace_length;"Lace length";info;0;0;0;0;;Length;CENTIMETER

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then there should be the following attributes:
      | type         | code         | label-en_US  | group     | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit |
      | text         | shortname    | Shortname    | info      | 0      | 1                      | 1           | 0        |                    |               |                     |
      | simpleselect | provider     | Provider     | info      | 0      | 1                      | 0           | 0        |                    |               |                     |
      | multiselect  | season       | Season       | info      | 0      | 1                      | 0           | 0        |                    |               |                     |
      | textarea     | commentary   | Commentary   | info      | 0      | 1                      | 1           | 1        |                    |               |                     |
      | prices       | public_price | Public price | marketing | 0      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | grade        | Grade        | marketing | 0      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | width        | Width        | sizes     | 0      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | hue          | Hue          | colors    | 0      | 1                      | 0           | 0        |                    |               |                     |
      | simpleselect | buckle_color | Buckle color | colors    | 0      | 1                      | 0           | 0        |                    |               |                     |
      | image        | image_upload | Image upload | media     | 0      | 0                      | 0           | 0        | gif,png            |               |                     |
      | date         | release      | Release date | info      | 0      | 1                      | 0           | 0        |                    |               |                     |
      | metric       | lace_length  | Lace length  | info      | 0      | 0                      | 0           | 0        |                    | Length        | CENTIMETER          |

  Scenario: Fail to change immutable properties of attributes during the import
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following CSV configuration to import:
      | type                   | pim_catalog_date | pim_catalog_metric |
      | code                   | release_date     | weight             |
      | unique                 | no               | no                 |
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

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip new attributes with invalid data during an import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
      pim_catalog_simpleselect;lace_color;"New lace color";colors;0;1;0;0;;;
      pim_catalog_metric;new_length;"New length";info;0;0;0;0;;Length;INVALID_LENGTH

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "skipped 1"
    And there should be the following attributes:
      | type         | code       | label-en_US    | group  | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit |
      | simpleselect | lace_color | New lace color | colors | 0      | 1                      | 0           | 0        |                    |               |                     |
    And there should be 23 attributes

  @jira https://akeneo.atlassian.net/browse/PIM-3266
  Scenario: Skip existing attributes with invalid data during an import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
      pim_catalog_simpleselect;lace_color;"New lace color";colors;0;1;0;0;;;
      pim_catalog_metric;length;"New length";info;0;0;0;0;;Length;INVALID_LENGTH

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "skipped 1"
    And there should be the following attributes:
      | type         | code       | label-en_US    | group  | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit |
      | simpleselect | lace_color | New lace color | colors | 0      | 1                      | 0           | 0        |                    |               |                     |
      | metric       | length     | Length         | info   | 0      | 0                      | 0           | 0        |                    | Length        | CENTIMETER          |


  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip attributes with empty code
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US
      pim_catalog_simpleselect;;"New lace color"
      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    And I should see "Field \"code\" must be filled"

  @jira https://akeneo.atlassian.net/browse/PIM-3786
  Scenario: Skip attributes with empty type
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group
      ;shortname;Shortname;info
      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "skipped 1"
    And I should see "attributeType: This value should not be blank."

  Scenario: Successfully import and update existing attribute
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;available_locales;sort_order;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;metric_family;default_metric_unit;max_file_size;allowed_extensions
      pim_catalog_simpleselect;manufacturer;Meine große Code;My awesome code;Mon super code;marketing;0;1;;;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000-12-12;2015-08-08;;EUR;452;jpg

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 1"
    Then I should see "processed 1"
    And there should be the following attributes:
      | type         | code         | label-en_US     | label-de_DE      | label-fr_FR    | group     | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | reference_data_name | localizable | scopable | available_locales | sort_order | max_characters | validation_rule | validation_regexp | wysiwyg_enabled | number_min | number_max | decimals_allowed | negative_allowed | date_min   | date_max   | metric_family | default_metric_unit | max_file_size | allowed_extensions |
      | simpleselect | manufacturer | My awesome code | Meine große Code | Mon super code | marketing | 0      | 1                      | 0           | 0        |                    |               |                     |                     | 0           | 0        | en_US,fr_FR       | 3          | 300            | validation_rule |                   | 1               | 3          | 5          | true             | true             | 2000-12-12 | 2015-08-08 |               | EUR                 | 452           | jpg                |

  Scenario: Fail to import attribute with invalid date format
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;available_locales;sort_order;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;metric_family;default_metric_unit;max_file_size;allowed_extensions
      pim_catalog_simpleselect;manufacturer;Meine große Code;My awesome code;Mon super code;marketing;0;1;;family;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000/12/12;2015/08/08;;EUR;452;jpg

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 1"
    Then I should see "skipped 1"
    Then I should see "Attribute expects a string with the format \"yyyy-mm-dd\" as data, \"2000/12/12\" given"

  Scenario: Fail to import attribute with invalid date
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;available_locales;sort_order;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;metric_family;default_metric_unit;max_file_size;allowed_extensions
      pim_catalog_simpleselect;manufacturer;Meine große Code;My awesome code;Mon super code;marketing;0;1;;family;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000-99-12;2015/08/08;;EUR;452;jpg

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 1"
    Then I should see "skipped 1"
    Then I should see "Invalid date, \"2000-99-12\" given"

  Scenario: Fail to import attribute with invalid data
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;available_locales;sort_order;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;metric_family;default_metric_unit;max_file_size;allowed_extensions
      pim_catalog_image;media_code;Meine große Code;My awesome code;Mon super code;marketing;0;1;;family;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000-08-08;2015-08-08;;EUR;not an int;jpg
      pim_catalog_image;media_code;Meine große Code;My awesome code;Mon super code;not a group;0;1;;family;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000-08-08;2015-08-08;;EUR;not an int;jpg

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 2"
    Then I should see "skipped 2"
    Then I should see "maxFileSize: This value should be 0.01 or more.: 0"
    Then I should see "AttributeGroup \"not a group\" does not exist"

  Scenario: Successfully import new attribute
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-de_DE;label-en_US;label-fr_FR;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;available_locales;sort_order;max_characters;validation_rule;validation_regexp;wysiwyg_enabled;number_min;number_max;decimals_allowed;negative_allowed;date_min;date_max;metric_family;default_metric_unit;max_file_size;allowed_extensions
      pim_catalog_simpleselect;myawesomecode;Meine große Code;My awesome code;Mon super code;marketing;0;1;;;;;0;0;en_US,fr_FR;3;300;validation_rule;;1;3;5;true;true;2000-12-12;2015-08-08;;EUR;452;jpg

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 1"
    Then I should see "created 1"
    And there should be the following attributes:
      | type         | code          | label-en_US     | label-de_DE      | label-fr_FR    | group     | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | reference_data_name | localizable | scopable | available_locales | sort_order | max_characters | validation_rule | validation_regexp | wysiwyg_enabled | number_min | number_max | decimals_allowed | negative_allowed | date_min   | date_max   | metric_family | default_metric_unit | max_file_size | allowed_extensions |
      | simpleselect | myawesomecode | My awesome code | Meine große Code | Mon super code | marketing | 0      | 1                      | 0           | 0        |                    |               |                     |                     | 0           | 0        | en_US,fr_FR       | 3          | 300            | validation_rule |                   | 1               | 3          | 5          | true             | true             | 2000-12-12 | 2015-08-08 |               | EUR                 | 452           | jpg                |

  Scenario: Fail to update an attribute with new immutable values
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit
      pim_catalog_text;sku;SKU;info;0;1;1;0;;;

      """
    And the following job "footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "footwear_attribute_import" job to finish
    Then I should see "read lines 1"
    Then I should see "skipped 1"
    Then I should see "attributeType: This property cannot be changed.: SKU"
    Then I should see "localizable: This property cannot be changed.: SKU"
    Then I should see "unique: This property cannot be changed.: SKU"
