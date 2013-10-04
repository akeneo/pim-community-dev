@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As a user
  I need to be able to see title depending of the catalog page

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully display the attribute index view title
    Given the following attribute:
      | code              | label             | type                 |
      | short_description | Short description | pim_catalog_textarea |
    And I am on the attributes page
    Then I should see the title "Product attributes"

  Scenario: Successfully display the attribute create view title
    Given I am on the attribute creation page
    Then I should see the title "Product attributes | Create"

  Scenario: Successfully display the attribute edit view title
    Given the following attribute:
      | code              | label             | type                 |
      | short_description | Short description | pim_catalog_textarea |
    And I edit the "short_description" attribute
    Then I should see the title "Product attributes Short description | Edit"

  Scenario: Successfully display the channel index view title
    Given the following channel:
      | code    | label   |
      | catalog | Catalog |
    And I am on the channels page
    Then I should see the title "Channels"

  Scenario: Successfully display the channel create view title
    Given I am on the channel creation page
    Then I should see the title "Channels | Create"

  Scenario: Successfully display the channel edit view title
    Given the following channel:
      | code    | label   |
      | catalog | Catalog |
    And I edit the "catalog" channel
    Then I should see the title "Channels Catalog | Edit"

  Scenario: Successfully display the currency index view title
    Given I am on the currencies page
    Then I should see the title "Currencies"

  Scenario: Successfully display the export index view title
    Given I am on the exports page
    Then I should see the title "Export management"

  Scenario: Successfully display the export show view title
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    And I am on the exports page
    When I click on the "acme_product_export" row
    Then I should see the title "Export Product export for Acme.com | Show"

  Scenario: Successfully display the export edit view title
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    And I am on the exports page
    When I click on the "acme_product_export" row
    And I press the "Edit" button
    Then I should see the title "Export Product export for Acme.com | Edit"

  Scenario: Successfully display the family index view title
    Given I am on the families page
    Then I should see the title "Families | Create"

  Scenario: Successfully display the family create view title
    Given I am on the family creation page
    Then I should see the title "Families | Create"

  Scenario: Successfully display the family edit view title
    Given the following family:
      | code   | label   |
      | tshirt | T-Shirt |
    And I edit the "tshirt" family
    Then I should see the title "Families T-Shirt | Edit"

  Scenario: Successfully display the group create view title
    Given I am on the group creation page
    Then I should see the title "Attribute groups | Create"

  Scenario: Successfully display the group edit view title
    Given the following attribute group:
      | code  | label |
      | sizes | Sizes |
    And I edit the "sizes" group
    Then I should see the title "Attribute groups Sizes | Edit"

  Scenario: Successfully display the import index view title
    Given I am on the imports page
    Then I should see the title "Import management"

  Scenario: Successfully display the import show view title
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And I am on the imports page
    When I click on the "acme_product_import" row
    Then I should see the title "Import Product import for Acme.com | Show"

  Scenario: Successfully display the import edit view title
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And I am on the imports page
    When I click on the "acme_product_import" row
    And I press the "Edit" button
    Then I should see the title "Import Product import for Acme.com | Edit"

  Scenario: Successfully display the locale index view title
    Given I am on the locales page
    Then I should see the title "Locales"

  Scenario: Successfully display the product index view title
    Given I am on the products page
    Then I should see the title "Products"

  Scenario: Successfully display the product edit view title
    Given the following product attributes:
      | label | required |
      | SKU   | yes      |
    And the following products:
      | sku   |
      | sku-1 |
    And I edit the "sku-1" product
    Then I should see the title "Products sku-1 | Edit"

  Scenario: Successfully display the variant index view title
    Given I am on the variants page
    Then I should see the title "Variant groups"
  
  Scenario: Successfully display the variant edit view title
    Given there is no variant
    And the following attribute:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_multiselect  |
    And the following variant:
      | code | label      | attributes |
      | MUG  | Mug Akeneo | color      |
    And I edit the "MUG" variant
    Then I should see the title "Variant groups Mug Akeneo | Edit"
