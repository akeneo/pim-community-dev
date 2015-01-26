@javascript
Feature: Enforce ACL on history
  In order to control who can view the history of different entities
  As an administrator
  I need to be able to define rights to see the history

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    Then removing the following permissions should hide the following history:
      | permission                    | page                                     |
      | View association type history | "X_SELL" association type                |
      | View attribute group history  | "Sizes" attribute group                  |
      | View attribute history        | "color" attribute                        |
      | View category history         | "sandals" category                       |
      | View channel history          | "mobile" channel                         |
      | View family history           | "boots" family                           |
      | View group history            | "similar_boots" product group            |
      | View product history          | "boot" product                           |
      | View export profile history   | "footwear_option_export" export job edit |
      | View import profile history   | "footwear_group_import" import job edit  |
