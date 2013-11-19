@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As a user
  I need to be able to see title depending of the catalog page

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the attribute page titles
    Given the following attribute:
      | code              | label             | type                 |
      | short_description | Short description | pim_catalog_textarea |
    When I am on the attributes page
    Then I should see the title "Product attributes"
    When I am on the attribute creation page
    Then I should see the title "Product attributes | Create"
    When I edit the "short_description" attribute
    Then I should see the title "Product attributes Short description | Edit"

  Scenario: Successfully display the channel page titles
    Given the following channel:
      | code    | label   |
      | catalog | Catalog |
    When I am on the channels page
    Then I should see the title "Channels"
    When I am on the channel creation page
    Then I should see the title "Channels | Create"
    When I edit the "catalog" channel
    Then I should see the title "Channels Catalog | Edit"

  Scenario: Successfully display the currency page titles
    Given I am on the currencies page
    Then I should see the title "Currencies"

  Scenario: Successfully display the export page titles
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    When I am on the exports page
    Then I should see the title "Export management"
    When I click on the "acme_product_export" row
    Then I should see the title "Export Product export for Acme.com | Show"
    When I press the "Edit" button
    Then I should see the title "Export Product export for Acme.com | Edit"

  Scenario: Successfully display the family page titles
    Given the following family:
      | code   | label   |
      | tshirt | T-Shirt |
    When I am on the families page
    Then I should see the title "Families | Create"
    When I am on the family creation page
    Then I should see the title "Families | Create"
    When I edit the "tshirt" family
    Then I should see the title "Families T-Shirt | Edit"

  Scenario: Successfully display the attribute group page titles
    Given the following attribute group:
      | code  | label |
      | sizes | Sizes |
    When I am on the attribute group creation page
    Then I should see the title "Attribute groups | Create"
    When I edit the "sizes" attribute group
    Then I should see the title "Attribute groups Sizes | Edit"

  Scenario: Successfully display the import page titles
    Given the following jobs:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    When I am on the imports page
    Then I should see the title "Import management"
    When I click on the "acme_product_import" row
    Then I should see the title "Import Product import for Acme.com | Show"
    When I press the "Edit" button
    Then I should see the title "Import Product import for Acme.com | Edit"

  Scenario: Successfully display the locale page titles
    Given I am on the locales page
    Then I should see the title "Locales"

  Scenario: Successfully display the product page titles
    Given the following product attributes:
      | label | required |
      | SKU   | yes      |
    And the following products:
      | sku   |
      | sku-1 |
    When I am on the products page
    Then I should see the title "Products"
    When I edit the "sku-1" product
    Then I should see the title "Products sku-1 | Edit"

  Scenario: Successfully display the variant page titles
    And the following attribute:
      | code  | label | type                    |
      | color | Color | pim_catalog_multiselect |
    And the following product group:
      | code | label      | attributes | type    |
      | MUG  | Mug Akeneo | color      | VARIANT |
    When I am on the variant groups page
    Then I should see the title "Variant groups"
    When I edit the "MUG" variant group
    Then I should see the title "Variant groups Mug Akeneo | Edit"
