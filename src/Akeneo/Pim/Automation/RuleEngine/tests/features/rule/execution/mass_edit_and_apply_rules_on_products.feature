Feature: Apply rules on products after a mass edit execution
  In order to have fully modified products after a mass edit
  As a product manager
  I need to have rules launched after a mass edit

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

  @integration-back
  Scenario: Successfully apply rules after a mass edit operation only on edited products
    When I execute an edit attribute values bulk action to set the en_US unscoped name to "tshirt" for "tshirt-github,tshirt-docker"
    Then the en_US unscoped name of "tshirt-github" should be "tshirt"
    And the en_US mobile description of "tshirt-github" should be "Generic t-shirt"
    And the en_US unscoped name of "tshirt-docker" should be "tshirt"
    And the en_US mobile description of "tshirt-docker" should be "Generic t-shirt"
    And the en_US unscoped name of "tshirt-jira" should be "tshirt"
    And the en_US mobile description of "tshirt-jira" should be "A pretty Jira t-shirt to practice spoon programming."
