Feature: Select product activated languages
  In order to provide translations of the product in some specific languages
  As an user
  I need to be able to select product activated languages

  Background:
    Given a "Car" product available in english and german
    And I am logged in as "admin"
    And I am on the "Car" product page

  Scenario: Successfully display activated languages for a product
    Given I visit the "Localisation" tab
    Then I should see that the product is available in English (United States) and German

  Scenario: Successfully select available languages for a product
    Given I visit the "Localisation" tab
    When I add the french language
    And I save the product
    Then I should see that the product is available in French, English (United States) and German
