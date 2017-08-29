@javascript
Feature: Create screenshot of all pages
  I would like to take a screenshot of all pages

  Background:
    Given the "footwear" catalog configuration
    Then I am logged in as "Julia"

  Scenario Outline: Take a screeenshot of a page
    Given I am on the <page> page
    Then I wait 10 seconds
    Then I take a screenshot with name "<name>"

    Examples:
      | page                                         | name |
      | "mobile" channel                             | channel |
      | "X_SELL" association type                    | association |
      | "Sizes" attribute group                      | attribute group |
      | "sandals" category                           | category |
      | "boots" family                               | family |
      | "similar_boots" product group                | product_group |
      | "caterpillar_boots" variant group            | variant_group |
      | "RELATED" group type                         | group_type |
      | "boot" product                               | product |
      | "color" attribute                            | attribute |
      | "csv_footwear_option_export" export job edit | export |
      | "csv_footwear_group_import" import job edit  | import |
