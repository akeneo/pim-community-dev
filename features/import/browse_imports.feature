@javascript
Feature: Browse imports
  In order to view the list of import job instances that have been created
  As a product manager
  I need to be able to view a list of them

  Scenario: Successfully view, sort and filter import jobs
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the imports page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see import profiles product_import, category_import, association_type_import, group_import, attribute_import and option_import
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
    And I should be able to use the following filters:
      | filter    | value                | result                                                                                                     |
      | Code      | at                   | association_type_import, attribute_import and category_import                                              |
      | Label     | Product              | product_import                                                                                             |
      | Job       | csv_group_import     | group_import                                                                                               |
      | Connector | Akeneo CSV Connector | product_import, category_import, association_type_import, group_import, attribute_import and option_import |
      | Status    | Ready                | product_import, category_import, association_type_import, group_import, attribute_import and option_import |
