@javascript
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
    And I am on the "tshirts" variant group page
    And I visit the "Attributes" tab

  Scenario: Successfully display attributes of a variant group
    Then I should see the Name and Description fields
    When I visit the "Additional information" group
    Then I should see the Manufacturer field

  Scenario: Successfully edit a simple attribute of a variant group
    When I visit the "Additional information" group
    And I fill in the following information:
      | Manufacturer | Lacoste |
    And I press the "Save" button
    Then the field Manufacturer should contain "Lacoste"

  Scenario: Successfully edit a localizable attribute of a variant group
    Given I switch the locale to "French (France)"
    And I fill in the following information:
      | Nom | French name |
    And I press the "Save" button
    And I switch the locale to "anglais (États-Unis)"
    And I fill in the following information:
      | Name | English name |
    And I press the "Save" button
    When I switch the locale to "French (France)"
    Then the field Nom should contain "French name"
    When I switch the locale to "anglais (États-Unis)"
    Then the field Name should contain "English name"

  Scenario: Successfully edit a localizable and scopable attribute of a variant group
    Given I switch the locale to "German (Germany)"
    And I expand the "Beschreibung" attribute
    And I fill in the following information:
      | ecommerce Beschreibung | German ecommerce description |
      | print Beschreibung     | German print description     |
    And I press the "Save" button
    And I switch the locale to "Englisch (Vereinigtes Königreich)"
    And I expand the "Description" attribute
    And I fill in the following information:
      | ecommerce Description | British ecommerce description |
      | tablet Description    | British tablet description    |
    And I press the "Save" button
    When I switch the locale to "German (Germany)"
    Then the field ecommerce Beschreibung should contain "German ecommerce description"
    Then the field print Beschreibung should contain "German print description"
    When I switch the locale to "Englisch (Vereinigtes Königreich)"
    Then the field ecommerce Description should contain "British ecommerce description"
    And the field tablet Description should contain "British tablet description"

  Scenario: Display a message when variant group has no attributes
    Given I am on the "jackets" variant group page
    And I visit the "Attributes" tab
    And I switch the locale to "French (France)"
    Then I should see "This variant group has no attributes in this locale"
    When I am on the "sweaters" variant group page
    And I visit the "Attributes" tab
    Then I should see "This variant group has no attributes yet"
