@javascript @skip
Feature: Edit attributes of a variant group
  In order to easily update attributes of products in a variant group
  As a product manager
  I need to be able to edit product attributes on the variant group page

  Background:
    Given a "apparel" catalog configuration
    And the following variant group values:
      | group   | attribute    | value            | locale | scope     |
      | tshirts | manufacturer | american_apparel |        |           |
      | tshirts | name         | a                | en_US  |           |
      | tshirts | name         | b                | en_GB  |           |
      | tshirts | name         | c                | fr_FR  |           |
      | tshirts | name         | d                | de_DE  |           |
      | tshirts | description  | e                | en_US  | ecommerce |
      | tshirts | description  | f                | en_GB  | ecommerce |
      | tshirts | description  | g                | fr_FR  | ecommerce |
      | tshirts | description  | h                | de_DE  | ecommerce |
      | tshirts | description  | i                | en_US  | tablet    |
      | tshirts | description  | j                | en_GB  | tablet    |
      | tshirts | description  | k                | en_US  | print     |
      | tshirts | description  | l                | de_DE  | print     |
      | jackets | description  | m                | en_US  | ecommerce |
    And I am logged in as "Julia"

  Scenario: Successfully display attributes of a variant group
    Given I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    Then I should see the Name and Description fields
    When I visit the "Additional information" group
    Then I should see the Manufacturer field

  Scenario: Successfully edit a simple attribute of a variant group
    Given I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    When I visit the "Additional information" group
    And I fill in the following information:
      | Manufacturer | Lacoste |
    And I save the variant group
    And I reload the page
    Then the field Manufacturer should contain "Lacoste"

  Scenario: Successfully edit a localizable attribute of a variant group
    Given I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    And I change the Nom for scope ecommerce and locale fr_FR to "French name"
    And I save the variant group
    And I change the Name for scope ecommerce and locale en_US to "English name"
    And I save the variant group
    And I reload the page
    When I switch the locale to "fr_FR"
    And I switch the scope to "ecommerce"
    Then the field Nom should contain "French name"
    When I switch the locale to "en_US"
    Then the field Name should contain "English name"

  Scenario: Successfully edit a localizable and scopable attribute of a variant group
    Given I am on the "tshirts" variant group page
    And I visit the "Attributes" tab
    And I change the Beschreibung for scope ecommerce and locale de_DE to "German ecommerce description"
    And I change the Beschreibung for scope print and locale de_DE to "German print description"
    And I save the variant group
    And I change the Description for scope ecommerce and locale en_GB to "British ecommerce description"
    And I change the Description for scope tablet and locale en_GB to "British tablet description"
    And I save the variant group
    And I reload the page
    When I switch the locale to "de_DE"
    And I switch the scope to "print"
    Then the variant group Beschreibung should be "German print description"
    When I switch the scope to "ecommerce"
    Then the variant group Beschreibung should be "German ecommerce description"
    When I switch the locale to "en_GB"
    Then the variant group Description should be "British ecommerce description"
    When I switch the scope to "tablet"
    Then the variant group Description should be "British tablet description"

  @skip @info Will be removed in PIM-6444
  Scenario: Display a message when variant group has no attributes
    Given I am on the "jackets" variant group page
    And I visit the "Attributes" tab
    And I switch the scope to "tablet"
    And I switch the locale to "fr_FR"
    Then I should see the text "This localizable field is not available for locale 'fr_FR' and channel 'tablet'"
    When I am on the "sweaters" variant group page
    And I visit the "Attributes" tab
    Then I should see the text "This variant group has no attributes yet"

  Scenario: Successfully save localized image attributes on variant group
    Given the following variant group values:
      | group    | attribute         | value                  | locale | scope |
      | sweaters | localizable_image | %fixtures%/akeneo.jpg  | en_US  |       |
      | sweaters | localizable_image | %fixtures%/akeneo2.jpg | en_GB  |       |
    And I am on the "sweaters" variant group page
    And I visit the "Attributes" tab
    When I switch the locale to "en_US"
    Then I should see the text "akeneo.jpg"
    When I switch the locale to "en_GB"
    Then I should see the text "akeneo2.jpg"
    And I save the variant group
    And I reload the page
    And I visit the "Attributes" tab
    When I switch the locale to "en_US"
    Then I should see the text "akeneo.jpg"
    When I switch the locale to "en_GB"
    Then I should see the text "akeneo2.jpg"
