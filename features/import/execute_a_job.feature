Feature: Execute a job
  In order to launch an import
  As a user
  I need to be able to execute a valid export

  Scenario: Fail to see the execute button of a job with validation errors
    Given the following job:
      | connector | alias          | code                | label                       | type   |
      | Akeneo    | product_import | acme_product_import | Product import for Acme.com | import |
    Given I am logged in as "admin"
    When I am on the "acme_product_import" import job page
    Then I should not see the "Execute" link
