@javascript
Feature: Publish a product
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully publish a product
    Given the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And I edit the "my-jacket" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I am on the published products grid
    Then the grid should contain 1 elements
    And I should see product my-jacket

  Scenario: Be able to edit the working copy of a publish product I can edit
    And the following published product:
      | sku       | family  | categories | name-en_US |
      | my-jacket | jackets | jackets    | Jacket1    |
    And I am on the "my-jacket" published product show page
    Then I should see the secondary action "Edit working copy"

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Fail to delete attribute options if it's used by a published product
    Given the following attributes:
      | code    | label-en_US | type                    | scopable | localizable | allowed_extensions | metric_family | default_metric_unit | group |
      | climate | Climate     | pim_catalog_multiselect | 0        | 0           |                    |               |                     | other |
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket |         | Jackets    |
    And the following "climate" attribute options: Hot and Cold
    And the following product values:
      | product   | attribute | value | locale | scope |
      | my-jacket | climate   | Hot   |        |       |
    And I edit the "my-jacket" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I am on the "climate" attribute page
    And I visit the "Options" tab
    When I remove the "Hot" option
    And I confirm the deletion
    Then I should see the text "Impossible to remove attribute option linked to a published product"
