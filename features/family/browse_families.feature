Feature: Browse families
  In order to view the families that have been created
  As a user
  I need to be able to view a list of them

  Scenario: Successfully display all the families
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    When I am on the families page
    Then I should see the families Boots, Sandals and Sneakers
