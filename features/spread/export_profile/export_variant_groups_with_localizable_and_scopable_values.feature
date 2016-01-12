Feature: Export variant groups
  In order to be able to access and modify groups data outside PIM
  As a product manager
  I need to be able to export variant groups

  # extracted from export_variant_group.feature to avoid issue with changing the catalog in a same feature
  @javascript
  Scenario: Successfully export variant groups with localizable and scopable attributes
  Given an "apparel" catalog configuration
  And the following attributes:
    | code                      | type | localizable | scopable |
    | localizable_text          | text | yes         | no       |
    | scopable_text             | text | no          | yes      |
    | localizable_scopable_text | text | yes         | yes      |
    | localizable_date          | date | yes         | no       |
    | scopable_date             | date | no          | yes      |
    | localizable_scopable_date | date | yes         | yes      |
  And the following variant group values:
    | group   | attribute                 | value      | locale | scope     |
    | tshirts | localizable_text          | text1      | en_US  |           |
    | tshirts | localizable_text          | text2      | fr_FR  |           |
    | tshirts | scopable_text             | text3      |        | ecommerce |
    | tshirts | scopable_text             | text4      |        | tablet    |
    | tshirts | localizable_scopable_text | text5      | en_US  | ecommerce |
    | tshirts | localizable_scopable_text | text6      | fr_FR  | ecommerce |
    | tshirts | localizable_scopable_text | text7      | en_US  | tablet    |
    | tshirts | localizable_scopable_text | text8      | en_GB  | tablet    |
    | tshirts | localizable_scopable_text | text9      | en_US  | print     |
    | tshirts | localizable_scopable_text | text10     | de_DE  | print     |
    | tshirts | localizable_date          | 2015-01-01 | en_US  |           |
    | tshirts | localizable_date          | 2015-01-02 | fr_FR  |           |
    | tshirts | scopable_date             | 2015-01-03 |        | ecommerce |
    | tshirts | scopable_date             | 2015-01-04 |        | tablet    |
    | tshirts | localizable_scopable_date | 2015-01-05 | en_US  | ecommerce |
    | tshirts | localizable_scopable_date | 2015-01-06 | fr_FR  | ecommerce |
    | tshirts | localizable_scopable_date | 2015-01-07 | en_US  | tablet    |
    | tshirts | localizable_scopable_date | 2015-01-08 | en_GB  | tablet    |
    | tshirts | localizable_scopable_date | 2015-01-09 | en_US  | print     |
    | tshirts | localizable_scopable_date | 2015-01-10 | de_DE  | print     |
  And the following job "variant_group_export" configuration:
    | filePath | %tmp%/variant_group_export/variant_group_export.csv |
  And I am logged in as "Julia"
  And I am on the "variant_group_export" export job page
  When I launch the export job
  And I wait for the "variant_group_export" job to finish
  Then I should see "Read 3"
  And I should see "Written 3"
  And exported file of "variant_group_export" should contain:
    """
    code;axis;label-de_DE;label-en_GB;label-en_US;label-fr_FR;localizable_date-en_US;localizable_date-fr_FR;localizable_scopable_date-de_DE-print;localizable_scopable_date-en_GB-tablet;localizable_scopable_date-en_US-ecommerce;localizable_scopable_date-en_US-print;localizable_scopable_date-en_US-tablet;localizable_scopable_date-fr_FR-ecommerce;localizable_scopable_text-de_DE-print;localizable_scopable_text-en_GB-tablet;localizable_scopable_text-en_US-ecommerce;localizable_scopable_text-en_US-print;localizable_scopable_text-en_US-tablet;localizable_scopable_text-fr_FR-ecommerce;localizable_text-en_US;localizable_text-fr_FR;scopable_date-ecommerce;scopable_date-tablet;scopable_text-ecommerce;scopable_text-tablet;type
    tshirts;color,size;T-Shirts;T-shirts;T-shirts;T-shirts;2015-01-01;2015-01-02;2015-01-10;2015-01-08;2015-01-05;2015-01-09;2015-01-07;2015-01-06;text10;text8;text5;text9;text7;text6;text1;text2;2015-01-03;2015-01-04;text3;text4;variant
    sweaters;color,size;Pullovern;Chandails;Sweaters;Sweaters;;;;;;;;;;;;;;;;;;;;;variant
    jackets;chest_size,color,waist_size;Jacken;Jackets;Jackets;Vestes;;;;;;;;;;;;;;;;;;;;;variant
    """
