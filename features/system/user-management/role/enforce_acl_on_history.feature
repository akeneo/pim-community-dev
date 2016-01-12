@javascript
Feature: Enforce ACL on history
  In order to control who can view the history of different entities
  As an administrator
  I need to be able to define rights to see the history

  Scenario: Successfully hide entity history when user doesn't have the rights
    Given a "footwear" catalog configuration
    And a "boot" product
    And I am logged in as "admin"
    Then removing the following permissions should hide the following section:
      | permission                    | section | page                                     |
      | View association type history | history | "X_SELL" association type                |
      | View attribute group history  | history | "Sizes" attribute group                  |
      | View attribute history        | history | "color" attribute                        |
      | View category history         | history | "sandals" category                       |
      | View channel history          | history | "mobile" channel                         |
      | View family history           | history | "boots" family                           |
      | View group history            | history | "similar_boots" product group            |
      | View product history          | history | "boot" product                           |
      | View export profile history   | history | "footwear_option_export" export job edit |
      | View import profile history   | history | "footwear_group_import" import job edit  |
