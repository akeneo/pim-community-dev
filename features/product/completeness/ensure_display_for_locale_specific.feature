@javascript
Feature: Proper completeness display for locale specific attributes
  In order to enrich the catalog
  As a regular user
  I need to be able to see if locale specific attributes are well displayed in the completeness panel

  Background:
    Given the "apparel" catalog configuration
    And the following attributes:
      | code                            | type             | localizable | available_locales | label-en_US     | group |
      | locale_specific                 | pim_catalog_text | 1           | de_DE,fr_FR,en_US | Locale Specific | other |
      | locale_specific_not_localizable | pim_catalog_text | 0           | de_DE,fr_FR       |                 | other |
    And the following family:
      | code | label-en_US | attributes                                            | requirements-ecommerce                            |
      | baz  | Baz         | sku,locale_specific,name                              | locale_specific,name                              |
      | biz  | Biz         | sku,locale_specific_not_localizable,name              | locale_specific_not_localizable,name              |
      | bar  | Bar         | sku,locale_specific_not_localizable,name,description  | locale_specific_not_localizable,name,description  |
      | bat  | Bat         | sku,name,description,thumbnail,legend,locale_specific | name,description,thumbnail,legend,locale_specific |
    And the following products:
      | sku | family |
      | foo | baz    |
      | bar | biz    |
      | baz | bat    |
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-5453
  Scenario: Well display completeness missing labels for product locale specific attributes
    Given I am on the "baz" product page
    When I open the "Completeness" panel
    And I switch the locale to "fr_FR"
    Then I should see the completeness:
      | locale | channel   | missing_values                                         |
      | fr_FR  | ecommerce | Nom, Description, Imagette, Légende, [locale_specific] |
      | de_DE  | ecommerce | Nom, Description, Imagette, Légende, [locale_specific] |
      | de_DE  | print     |                                                        |
      | en_GB  | ecommerce | Nom, Description, Imagette, Légende                    |
      | en_GB  | tablet    |                                                        |
      | en_US  | ecommerce | Nom, Description, Imagette, Légende, [locale_specific] |
      | en_US  | print     |                                                        |
      | en_US  | tablet    |                                                        |
    When I switch the locale to "de_DE"
    Then I should see the completeness:
      | locale | channel   | missing_values                                                  |
      | de_DE  | ecommerce | Name, Beschreibung, Miniaturansicht, Legende, [locale_specific] |
      | de_DE  | print     |                                                                 |
      | en_GB  | ecommerce | Name, Beschreibung, Miniaturansicht, Legende                    |
      | en_GB  | tablet    |                                                                 |
      | en_US  | ecommerce | Name, Beschreibung, Miniaturansicht, Legende, [locale_specific] |
      | en_US  | print     |                                                                 |
      | en_US  | tablet    |                                                                 |
      | fr_FR  | ecommerce | Name, Beschreibung, Miniaturansicht, Legende, [locale_specific] |
