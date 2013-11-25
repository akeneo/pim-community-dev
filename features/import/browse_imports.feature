@javascript
Feature: Browse imports
  In order to view the list of import job instances that have been created
  As a user
  I need to be able to view a list of them

  Scenario: Successfully view, sort and filter import jobs
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label, Job, Connector and Status
    And I should see import profiles footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
    And I should be able to use the following filters:
      | filter    | value                | result                                                                                                                                                      |
      | Code      | at                   | footwear_association_import, footwear_attribute_import and footwear_category_import                                                                         |
      | Label     | Product              | footwear_product_import                                                                                                                                     |
      | Job       | group_import         | footwear_group_import                                                                                                                                       |
      | Connector | Akeneo CSV Connector | footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import |
      | Status    | Ready                | footwear_product_import, footwear_category_import, footwear_association_import, footwear_group_import, footwear_attribute_import and footwear_option_import |
