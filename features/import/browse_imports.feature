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
    Then I should see the columns Label, Job, Connector and Status
    And I should see import profiles product_import, category_import, association_type_import, group_import, attribute_import, option_import and xlsx_product_import
    And the rows should be sorted ascending by Label
    And I should be able to sort the rows by Label, Connector and Status

  @ce
  Scenario Outline: Successfully filter import jobs with values
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter    | operator | value                | result                                                                                                                          | count |
      | job_name  | contains | Group import in CSV  | group_import                                                                                                                    | 1     |
      | connector | contains | Akeneo CSV Connector | product_import, category_import, association_type_import, group_import, attribute_import, option_import                         | 6     |
      | status    | contains | Ready                | product_import, category_import, association_type_import, group_import, attribute_import, option_import and xlsx_product_import | 7     |

  @ce
  Scenario: Successfully search on label
    When I search "product"
    Then the grid should contain 2 elements
    And I should see entities product_import and xlsx_product_import
