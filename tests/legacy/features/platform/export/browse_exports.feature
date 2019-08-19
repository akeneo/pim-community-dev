@javascript
Feature: Browse export profiles
  In order to view the list of export jobs that have been created
  As a product manager
  I need to be able to view a list of them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports grid

  Scenario Outline: Successfully filter export jobs
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter    | operator | value                | result                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | count |
      | job_name  |          | Group export in CSV  | CSV footwear group export                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | 1     |
      | connector |          | Akeneo CSV Connector | CSV footwear product export, CSV footwear category export, CSV footwear association type export, CSV footwear group export, CSV footwear attribute export, CSV footwear family export, CSV footwear attribute group export, CSV footwear channel export, CSV footwear currency export, CSV footwear group type export, CSV footwear locale export and CSV footwear option export                                                                                                                                                                                                                                                                                                                                  | 12    |
  Scenario: Successfully search on label
    When I search "product"
    Then the grid should contain 1 element
    And I should see entity CSV footwear product export
