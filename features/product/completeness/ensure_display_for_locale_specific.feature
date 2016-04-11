@javascript
Feature: Proper completeness display for locale specific attributes
  In order to enrich the catalog
  As a regular user
  I need to be able to see if locale specific attributes are well displayed in the completeness panel

  Background:
    Given the "apparel" catalog configuration
    And the following attributes:
      | code                            | type | localizable | available_locales | label-en_US     |
      | locale_specific                 | text | yes         | de_DE,fr_FR,en_US | Locale Specific |
      | locale_specific_not_localizable | text | no          | de_DE,fr_FR       |                 |
    And the following family:
      | code | label-en_US | attributes                                                 | requirements-ecommerce                                |
      | baz  | Baz         | sku, locale_specific, name                                 | locale_specific, name                                 |
      | biz  | Biz         | sku, locale_specific_not_localizable, name                 | locale_specific_not_localizable, name                 |
      | bar  | Bar         | sku, locale_specific_not_localizable, name, description    | locale_specific_not_localizable, name, description    |
      | bat  | Bat         | sku, name, description, thumbnail, legend, locale_specific | name, description, thumbnail, legend, locale_specific |
    And the following products:
      | sku    | family |
      | foo    | baz    |
      | bar    | biz    |
      | baz    | bat    |
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-4771
  Scenario: Well display completeness for locale specific attributes
    Given I am on the "foo" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel   | locale | state   | missing_values       | ratio |
      | ecommerce | de_DE  | warning | name                 | 33%   |
      | print     | de_DE  | success |                      | 100%  |
      | ecommerce | en_GB  | warning | name                 | 50%   |
      | tablet    | en_GB  | success |                      | 100%  |
      | ecommerce | en_US  | warning | locale_specific name | 33%   |
      | print     | en_US  | success |                      | 100%  |
      | tablet    | en_US  | success |                      | 100%  |
      | ecommerce | fr_FR  | warning | name                 | 33%   |

  @jira https://akeneo.atlassian.net/browse/PIM-4771
  Scenario: Well display completeness for locale specific attributes
    Given I am on the "bar" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel   | locale | state   | missing_values                       | ratio |
      | ecommerce | de_DE  | warning | locale_specific_not_localizable name | 33%   |
      | print     | de_DE  | success |                                      | 100%  |
      | ecommerce | en_GB  | warning | name                                 | 50%   |
      | tablet    | en_GB  | success |                                      | 100%  |
      | ecommerce | en_US  | warning | name                                 | 50%   |
      | print     | en_US  | success |                                      | 100%  |
      | tablet    | en_US  | success |                                      | 100%  |
      | ecommerce | fr_FR  | warning | locale_specific_not_localizable name | 33%   |

  @jira https://akeneo.atlassian.net/browse/PIM-5453
  Scenario: Well display completeness missing labels for product locale specific attributes
    Given I am on the "baz" product page
    When I open the "Completeness" panel
    And I switch the locale to "fr_FR"
    Then I should see the missing completeness labels:
      | channel   | locale | missing_value   | expected_label    |
      | ecommerce | fr_FR  | name            | Nom               |
      | ecommerce | fr_FR  | description     | Description       |
      | ecommerce | de_DE  | thumbnail       | Imagette          |
      | ecommerce | fr_FR  | legend          | Légende           |
      | ecommerce | fr_FR  | locale_specific | [locale_specific] |
    When I switch the locale to "de_DE"
    Then I should see the missing completeness labels:
      | channel   | locale | missing_value   | expected_label    |
      | ecommerce | de_DE  | description     | Beschreibung      |
      | ecommerce | fr_FR  | description     | Beschreibung      |
      | ecommerce | de_DE  | thumbnail       | Miniaturansicht   |
      | ecommerce | fr_FR  | thumbnail       | Miniaturansicht   |
      | ecommerce | de_DE  | legend          | Legende           |
      | ecommerce | fr_FR  | legend          | Legende           |
      | ecommerce | fr_FR  | locale_specific | [locale_specific] |
    When I switch the locale to "en_US"
    Then I should see the missing completeness labels:
      | channel   | locale | missing_value   | expected_label  |
      | ecommerce | en_US  | locale_specific | Locale Specific |
