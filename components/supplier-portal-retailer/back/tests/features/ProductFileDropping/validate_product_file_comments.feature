Feature: Supplier Portal - Product File Dropping - Comment a product file
  In order to collaborate with Jimmy
  As Julia
  I can comment a product file

  Background:
    Given a supplier

  Scenario: Validates that a comment cannot be empty
    Given a product file
    When I comment it with " "
    Then I should have an error message telling that the comment should not be empty

  Scenario: Validates that a comment cannot exceed 255 characters
    Given a product file
    When I comment it with a too long comment
    Then I should have an error message telling that the comment should not exceed 255 characters

  Scenario: Validates that we cannot have more than 50 comments on the same product file
    Given a product file with 50 comments
    When I comment it with "Another comment, again!"
    Then I should have an error message telling that the product file cannot have more than 50 comments

  Scenario: Comment a product file
    Given a product file
    When I comment it with "foo"
    Then the product file contains the comment "foo"
