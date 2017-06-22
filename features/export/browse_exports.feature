@javascript
Feature: Browse export profiles
  In order to view the list of export jobs that have been created
  As a product manager
  I need to be able to view a list of them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports page
    And I change the page size to 100

  Scenario: Successfully view and sort export jobs
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see export profiles csv_footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, csv_footwear_group_export, csv_footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_option_export, xlsx_footwear_family_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_export and xlsx_footwear_option_export
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label, Connector and Status

  Scenario Outline: Successfully filter export jobs
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter    | operator | value                | result                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | count |
      | code      | contains | at                   | csv_footwear_association_type_export, csv_footwear_attribute_export, csv_footwear_category_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_group_export, csv_footwear_attribute_group_export and csv_footwear_attribute_export                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | 8     |
      | job_name  |          | Group export in CSV  | csv_footwear_group_export                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | 1     |
      | connector |          | Akeneo CSV Connector | csv_footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, csv_footwear_group_export, csv_footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export, csv_footwear_attribute_group_export, csv_footwear_channel_export, csv_footwear_currency_export, csv_footwear_group_type_export, csv_footwear_locale_export and csv_footwear_option_export                                                                                                                                                                                                                                                                                                                                  | 13    |
      | status    |          | Ready                | csv_footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, csv_footwear_group_export, csv_footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export, csv_footwear_option_export, xlsx_footwear_family_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_export, csv_footwear_attribute_group_export, csv_footwear_channel_export, csv_footwear_currency_export, csv_footwear_group_type_export, csv_footwear_locale_export, xlsx_footwear_attribute_group_export, xlsx_footwear_channel_export, xlsx_footwear_currency_export, xlsx_footwear_group_type_export, xlsx_footwear_locale_export and xlsx_footwear_option_export | 23    |

  Scenario: Successfully search on label
    When I search "product"
    Then the grid should contain 1 element
    And I should see entity csv_footwear_product_export
