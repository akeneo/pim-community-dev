@javascript
Feature: Sort variants
  In order to sort variants in the catalog
  As a user
  I need to be able to sort variants by several columns in the catalog

  Background:
    Given there is no variant
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following variants:
      | code          | label          | attributes  |
      | TSHIRT_ORO    | T-Shirt Oro    | size, color |
      | MUG           | Mug Akeneo     | color       |
      | TSHIRT_AKENEO | T-Shirt Akeneo | size        |
    And I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the variants page
    Then the rows should be sortable by code and label
    And the rows should be sorted ascending by code
    And I should see sorted variants MUG, TSHIRT_AKENEO and TSHIRT_ORO

  Scenario: Successfully sort variants by code
    Given I am on the variants page
    When I sort by "code" value ascending
    Then I should see sorted variants MUG, TSHIRT_AKENEO and TSHIRT_ORO
    When I sort by "code" value descending
    Then I should see sorted variants TSHIRT_ORO, TSHIRT_AKENEO and MUG

  Scenario: Successfully sort variants by label
    Given I am on the variants page
    When I sort by "label" value ascending
    Then I should see sorted variants MUG, TSHIRT_AKENEO and TSHIRT_ORO
    When I sort by "label" value descending
    Then I should see sorted variants TSHIRT_ORO, TSHIRT_AKENEO and MUG
