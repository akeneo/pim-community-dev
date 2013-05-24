Feature: Select product activated languages
  In order to provide translations of the product in some specific languages
  As an user
  I need to be able to select product activated languages

  Background:
    Given a "Car" product available in french and german
    And availabe languages are french, german and english
    And I am logged in as "admin"
    And I am on the "Car" product page

  Scenario: Successfully display activated languages for a product
    Given I visit the "Localisation" tab
    Then I should see that the product is available in french and german

  Scenario: Successfully select available languages for a product
    Given I visit the "Localisation" tab
    When I select english language
    And I save the product
    Then I should see that the product is available in french, english and german
