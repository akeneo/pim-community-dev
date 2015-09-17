@javascript
Feature: Proper completeness display for locale specific attributes
  In order to enrich the catalog
  As a regular user
  I need to be able to see if locale specific attributes are well displayed in the completeness panel

  Background:
    Given the "apparel" catalog configuration
    And the following attributes:
      | code                            | type | localizable | available_locales |
      | locale_specific                 | text | yes         | en_US             |
      | locale_specific_not_localizable | text | no          | de_DE,fr_FR       |
    And the following family:
      | code | label-en_US | attributes                                 | requirements-ecommerce                |
      | baz  | Baz         | sku, locale_specific, name                 | locale_specific, name                 |
      | biz  | Biz         | sku, locale_specific_not_localizable, name | locale_specific_not_localizable, name |
    And the following products:
      | sku | family |
      | foo | baz    |
      | bar | biz    |

    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-4771
  Scenario: Well display completeness for locale specific attributes
    Given I am on the "foo" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel   | locale | state   | missing_values       | ratio |
      | ecommerce | de_DE  | warning | name                 | 50%   |
      | print     | de_DE  | success |                      | 100%  |
      | ecommerce | en_GB  | warning | name                 | 50%   |
      | tablet    | en_GB  | success |                      | 100%  |
      | ecommerce | en_US  | warning | locale_specific name | 33%   |
      | print     | en_US  | success |                      | 100%  |
      | tablet    | en_US  | success |                      | 100%  |
      | ecommerce | fr_FR  | warning | name                 | 50%   |

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
