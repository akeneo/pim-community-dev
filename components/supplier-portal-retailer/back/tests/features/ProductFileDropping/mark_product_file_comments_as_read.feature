Feature: Supplier Portal - Product File Dropping - Mark product file comments as read for a supplier

  Background:
    Given a supplier

  Scenario: A contributor marks product file comments as read
    Given a product file with 50 comments
    And contributors have not read the comments yet
    When a contributor marks all comments of a product file as read
    Then all comments should be marked as read for this product file

