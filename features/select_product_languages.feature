Feature: Select product activated languages
  In order to provide translations of the product in some specific languages
  As an user
  I need to be able to select product activated languages

  Background:
    Given a "Car" product
    And availabe languages are french, german and english
    And I am logged in as "Admin"

  Scenario: Successfully display available languages for a product
    Given I visit the "Localisation" tab
    Then I should see french, german and english languages

  Scenario: Successfully select available languages for a product
    Given I visit the "Localisation" tab
    When I select german and english languages
    And I press "Save"
    Then I should see that the product is available in french and english
