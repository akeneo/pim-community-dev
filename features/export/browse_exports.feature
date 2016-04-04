@javascript
Feature: Browse export profiles
  In order to view the list of export jobs that have been created
  As a product manager
  I need to be able to view a list of them

  Scenario: Successfully view, sort and filter export jobs
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports page
    And I change the page size to 100
    Then the grid should contain 13 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see export profiles footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, footwear_group_export, footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_option_export, xlsx_footwear_family_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_export and xlsx_footwear_option_export
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label, Connector and Status
    And I should be able to use the following filters:
      | filter    | value                | result                                                                                                                                                                                                                                                                                                                                                                                                      |
      | Code      | at                   | csv_footwear_association_type_export, csv_footwear_attribute_export, csv_footwear_category_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export and csv_footwear_attribute_export                                                                                                                                                                                                   |
      | Label     | Product              | footwear_product_export                                                                                                                                                                                                                                                                                                                                                                                     |
      | Job       | Group export in CSV  | footwear_group_export                                                                                                                                                                                                                                                                                                                                                                                       |
      | Connector | Akeneo CSV Connector | footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, footwear_group_export, footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export and csv_footwear_option_export                                                                                                                                                                 |
      | Status    | Ready                | footwear_product_export, csv_footwear_category_export, csv_footwear_association_type_export, footwear_group_export, footwear_variant_group_export, csv_footwear_attribute_export, csv_footwear_family_export, csv_footwear_option_export, xlsx_footwear_family_export, xlsx_footwear_category_export, xlsx_footwear_association_type_export, xlsx_footwear_attribute_export and xlsx_footwear_option_export |
