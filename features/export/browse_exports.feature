@javascript
Feature: Browse export profiles
  In order to view the list of export jobs that have been created
  As a product manager
  I need to be able to view a list of them

  Scenario: Successfully view, sort and filter export jobs
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see export profiles footwear_product_export, footwear_category_export, footwear_association_type_export, footwear_group_export, footwear_attribute_export and footwear_option_export
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
    And I should be able to use the following filters:
      | filter    | value                | result                                                                                                                                                           |
      | Code      | at                   | footwear_association_type_export, footwear_attribute_export and footwear_category_export                                                                         |
      | Label     | Product              | footwear_product_export                                                                                                                                          |
      | Job       | csv_group_export     | footwear_group_export                                                                                                                                            |
      | Connector | Akeneo CSV Connector | footwear_product_export, footwear_category_export, footwear_association_type_export, footwear_group_export, footwear_attribute_export and footwear_option_export |
      | Status    | Ready                | footwear_product_export, footwear_category_export, footwear_association_type_export, footwear_group_export, footwear_attribute_export and footwear_option_export |
