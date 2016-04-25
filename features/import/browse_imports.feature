@javascript
Feature: Browse imports
  In order to view the list of import job instances that have been created
  As a product manager
  I need to be able to view a list of them

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the imports page

  @ce
  Scenario: Successfully view and sort import jobs
    Then I should see the columns Code, Label, Job, Connector and Status
    And I should see import profiles product_import, category_import, association_type_import, group_import, variant_group_import, attribute_import, option_import and xlsx_product_import
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label, Connector and Status

  Scenario: Successfully filter import jobs with values
    Then I should be able to use the following filters:
      | filter    | value                | result                                                                                                                                                |
      | Code      | at                   | association_type_import, attribute_import and category_import                                                                                         |
      | Label     | Product              | product_import and xlsx_product_import                                                                                                                |
      | Job       | Group import in CSV  | group_import                                                                                                                                          |
      | Connector | Akeneo CSV Connector | product_import, category_import, association_type_import, variant_group_import, group_import, attribute_import, option_import                         |
      | Status    | Ready                | product_import, category_import, association_type_import, variant_group_import, group_import, attribute_import, option_import and xlsx_product_import |
