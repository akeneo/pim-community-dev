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

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4762
  Scenario: Not be able to edit the working copy of a publish product I can't edit
    And the following published product:
      | sku    | family | categories | name-en_US |
      | my-tee | tees   | tshirts    | Tee1       |
    And I am on the "my-tee" published product show page
    Then I should not see "Edit working copy"

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4762
  Scenario: Successfully publish a product containing attributes
    Given the following attributes:
      | code      | label-en_US | type             | scopable | unique | date_min   | date_max   | group |
      | release   | Release     | pim_catalog_date | 0        | 1      | 2013-01-01 | 2015-12-12 | info  |
      | available | Available   | pim_catalog_date | 1        | 0      | 2013-01-01 | 2015-12-12 | info  |
    And the following attributes:
      | code       | label-en_US | type               | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | max_length | Length      | pim_catalog_metric | 1        | Length        | METER               | 0                | 0                |            |            | info  |
    And the following attributes:
      | code       | label-en_US | type               | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group |
      | popularity | Popularity  | pim_catalog_number | 1        | 0      | 0                | 0                | 1          | 10         | info  |
    And the following attributes:
      | code    | label-en_US | type                         | scopable | negative_allowed | decimals_allowed | number_min | number_max | group |
      | customs | Customs     | pim_catalog_price_collection | 1        |                  | 1                | 10         | 100        | info  |
    And the following attributes:
      | code    | label-en_US | type              | allowed_extensions | group |
      | picture | Picture     | pim_catalog_image | jpg                | other |
      | manual  | Manual      | pim_catalog_file  | txt                | other |
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And the following family:
      | code | label-en_US | attributes                                          |
      | baz  | Baz         | sku,release,available,max_length,popularity,customs |
    And the following product values:
      | product   | attribute  | value                 | scope  |
      | my-jacket | release    | 2013-02-02            |        |
      | my-jacket | available  | 2013-10-03            | tablet |
      | my-jacket | max_length | 25 CENTIMETER         | tablet |
      | my-jacket | popularity | 9                     | tablet |
      | my-jacket | customs    | 100 EUR               | tablet |
      | my-jacket | picture    | %fixtures%/akeneo.jpg |        |
      | my-jacket | manual     | %fixtures%/akeneo.txt |        |
    And I edit the "my-jacket" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I am on the published products grid
    Then the grid should contain 1 elements
    And I should see product my-jacket
    And I am on the "my-jacket" published product show page
    And I should see the text "Release"
    And I should see the text "February 02, 2013"
    And I should see the text "Available"
    And I should see the text "October 03, 2013"
    And I should see the text "Length"
    And I should see the text "25.0000 CENTIMETER"
    And I should see the text "Popularity"
    And I should see the text "9"
    And I should see the text "Customs"
    And I should see the text "100.00 EUR"
    And I should see the text "Picture"
    And I should see the text "akeneo.jpg"
    And I should see the text "Manual"
    And I should see the text "akeneo.txt"
    Given I edit the "my-jacket" product
    And I change the Release to "2014-03-25"
    And I visit the "Other" group
    And I remove the "Picture" file
    And I attach file "akeneo2.jpg" to "Picture"
    And I save the product
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I am on the published products grid
    Then the grid should contain 1 elements
    And I should see product my-jacket
    And I am on the "my-jacket" published product show page
    And I should see the text "March 25, 2014"
    And I should see the text "akeneo2.jpg"
    And I should not see "February 02, 2013"
    And I should not see "akeneo.jpg"

  @jira https://akeneo.atlassian.net/browse/PIM-5996
  Scenario: Successfully publish a product containing boolean attributes
    Given the following attributes:
      | code       | label-en_US | type                | group |
      | waterproof | Waterproof  | pim_catalog_boolean | other |
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
    And the following family:
      | code | label-en_US | attributes     |
      | baz  | Baz         | sku,waterproof |
    And the following product values:
      | product   | attribute  | value |
      | my-jacket | handmade   | 1     |
      | my-jacket | waterproof | 0     |
    And I edit the "my-jacket" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    Then attribute Handmade of published "my-jacket" should be "true"
    And attribute Waterproof of published "my-jacket" should be "false"

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Fail to delete attribute options if it's used by a published product
    Given the following attributes:
      | code    | label-en_US | type                    | scopable | localizable | allowed_extensions | metric_family | default_metric_unit | group |
      | climate | Climate     | pim_catalog_multiselect | 0        | 0           |                    |               |                     | other |
    And the following product:
      | sku       | family  | name-en_US |
      | my-jacket | jackets | Jackets    |
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
