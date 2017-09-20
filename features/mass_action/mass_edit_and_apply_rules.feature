@javascript
Feature: Apply rules after a mass edit have run
  In order to have fully modified products after a mass edit
  As a product manager
  I need to have rules launched on a product mass edit

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku           | family | name-en_US    | description-en_US-mobile                             |
      | tshirt-github | tees   | GitHub tshirt | A nice GitHub t-shirt with the Octocat!              |
      | tshirt-docker | tees   | Docker tshirt | A nice Docker t-shirt with a wale!                   |
      | tshirt-jira   | tees   | tshirt        | A pretty Jira t-shirt to practice spoon programming. |
    And the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field:    name
            locale:   en_US
            operator: =
            value:    tshirt
        actions:
          - type:   set
            field:  description
            value:  Generic t-shirt
            locale: en_US
            scope:  mobile
      """
    And I am logged in as "Julia"

  Scenario: Successfully apply rules after a mass edit operation only on edited products
    When I am on the products grid
    And I select rows tshirt-github, tshirt-docker
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I visit the "Product information" group
    And I change the "Name" to "tshirt"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product "tshirt-github" should have the following values:
      | name-en_US               | tshirt          |
      | description-en_US-mobile | Generic t-shirt |
    And the product "tshirt-docker" should have the following values:
      | name-en_US               | tshirt          |
      | description-en_US-mobile | Generic t-shirt |
    But the product "tshirt-jira" should have the following values:
      | name-en_US               | tshirt                                               |
      | description-en_US-mobile | A pretty Jira t-shirt to practice spoon programming. |
