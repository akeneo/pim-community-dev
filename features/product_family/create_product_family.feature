Feature: Product family creation
  In order to provide a new family for a new type of product
  As a user
  I need to be able to create a family

  Scenario: Succesfully create a family
    Given I am logged in as "admin"
    When I am on the family creation page
    And I change the Code to "computer"
    And I change the default Label to "Computer"
    And I save the family
    Then I should see "Product family successfully created"
    And I should be on the "Computer" family page
