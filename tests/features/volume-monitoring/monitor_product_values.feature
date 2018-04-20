Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of product values

  @acceptance-back
  Scenario: Monitor the number of product values
    Given a catalog with 101 product values
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of product values is 101

  @acceptance-back
  Scenario: Monitor the average number of product values per product
    Given a product with 10 product values
    And a product model with 4 product values
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of product values per product is 7
    And the report returns that the maximum number of product values per product is 10

