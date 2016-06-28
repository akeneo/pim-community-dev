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
    Then I should be able to use the following filters:
      | filter    | operator    | value                                | result                                                                                                                                                                                                                                                                                                                                                                                                                  |
      | code      | contains    | at                                   | csv_footwear_association_type_export, csv_footwear_attribute_export, csv_footwear_category_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export and csv_footwear_attribute_export                                                                                                                                                                                                               |
      | label     | contains    | Product                              | csv_footwear_product_export                                                                                                                                                                                                                                                                                                                                                                                             |
      | job_name  |             | Group export in CSV                  | csv_footwear_group_export                                                                                                                                                                                                                                                                                                                                                                                               |
      | connector |             | Akeneo CSV Connector                 | csv_footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, csv_footwear_group_export, csv_footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export and csv_footwear_option_export                                                                                                                                                                 |
      | status    |             | Ready                                | csv_footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, csv_footwear_group_export, csv_footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export, csv_footwear_option_export, xlsx_footwear_family_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_export and xlsx_footwear_option_export |
      | code      | is equal to | csv_footwear_association_type_export | csv_footwear_association_type_export                                                                                                                                                                                                                                                                                                                                                                                    |
      | label     | is equal to | CSV footwear product export          | csv_footwear_product_export                                                                                                                                                                                                                                                                                                                                                                                             |
