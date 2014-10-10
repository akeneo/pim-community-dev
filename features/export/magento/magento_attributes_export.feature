@javascript
Feature: Magento attribute export
  In order to view attributes in Magento
  As an Administrator
  I need to be able to export attributes to Magento

  Scenario: Successfully export attributes to Magento
    Given a "magento" catalog configuration
    And the following attribute group:
      | code  | label-en_US | label-fr_FR |
      | size  | Size        | Taille      |
      | color | Color       | Couleur     |
      | other | Others      | Autres      |
    And the following attributes:
      | code              | type         | localizable | scopable | useable as grid filter| metric family | default metric unit | group   |
      | color             | simpleselect | yes         | yes      | yes                   |               |                     | color   |
      | size              | simpleselect | yes         | yes      | yes                   |               |                     | size    |
      | sku               | identifier   | no          | yes      | yes                   |               |                     | other   |
      | name              | text         | yes         | yes      | yes                   |               |                     | other   |
      | short_description | textarea     | yes         | yes      | yes                   |               |                     | other   |
      | description       | textarea     | yes         | yes      | yes                   |               |                     | other   |
      | price             | prices       | yes         | yes      | yes                   |               |                     | other   |
      | weight            | metric       | yes         | yes      | yes                   | Weight        | GRAM                | other   |
      | tax_class_id      | number       | no          | yes      | yes                   |               |                     | other   |
    And the following families:
      | code  | attributes                                                                          |
      | Shirt | color, size, sku, name, short_description, description, price, weight, tax_class_id |
    And the following attribute label translations:
      | attribute         | locale  | label                   |
      | color             | french  | Couleur                 |
      | color             | english | Color                   |
      | size              | french  | Taille                  |
      | size              | english | Size                    |
      | name              | french  | Nom                     |
      | name              | english | Name                    |
      | short_description | french  | Courte description      |
      | short_description | english | Short description       |
      | description       | french  | Description             |
      | description       | english | Description             |
      | price             | french  | Prix                    |
      | price             | english | Price                   |
      | weight            | french  | Poids                   |
      | weight            | english | Weight                  |
      | tax_class_id      | french  | Id de la classe de taxe |
      | tax_class_id      | english | Tax class id            |
    And the following "size" attribute options: XS, S, M, L and XL
    And the following "color" attribute options: red, blue, black
    And the following attribute options translations:
      | attribute_option | locale  | value |
      | red              | french  | Rouge |
      | red              | english | Red   |
      | blue             | french  | Bleu  |
      | blue             | english | Blue  |
      | black            | french  | Noir  |
      | black            | english | Black |
      | XS               | french  | XS    |
      | XS               | english | XS    |
      | S                | french  | S     |
      | S                | english | S     |
      | M                | french  | M     |
      | M                | english | M     |
      | L                | french  | L     |
      | L                | english | L     |
    And I am logged in as "peter"
    When I am on the "magento_attributeset_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_attributeset_export" job to finish
    When I am on the "magento_attribute_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I press the "Save" button and I wait "30"s
    Then I launch the export job
    And I wait for the "magento_attribute_export" job to finish
    When I am on the "magento_option_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I press the "Save" button and I wait "30"s
    Then I launch the export job
    And I wait for the "magento_option_export" job to finish
    Then I check if "attributes" were sent in Magento:
      | attribute_code    | store_view         | title                   | options           | frontend_input | is_global  | is_unique | is_required | is_searchable |
      | color             | Admin              | color                   | red, black, blue  | Dropdown       | Store View | No        | No          | Yes           |
      | color             | Default Store View | Color                   | Red, Black, Blue  |                |            |           |             |               |
      | color             | fr_fr              | Couleur                 | Rouge, Noir, Bleu |                |            |           |             |               |
      | size              | Admin              | size                    | XS, S, M, L       | Dropdown       | Store View | No        | No          | Yes           |
      | size              | Default Store View | Size                    | XS, S, M, L       |                |            |           |             |               |
      | size              | fr_fr              | Taille                  | XS, S, M, L       |                |            |           |             |               |
      | sku               | Admin              | SKU                     |                   | Text Field     | Global     | Yes       | Yes         | Yes           |
      | name              | Admin              | name                    |                   | Text Field     | Store View | No        | Yes         | Yes           |
      | name              | Default Store View | Name                    |                   |                |            |           |             |               |
      | name              | fr_fr              | Nom                     |                   |                |            |           |             |               |
      | short_description | Admin              | short_description       |                   | Text Area      | Store View | No        | Yes         | Yes           |
      | short_description | Default Store View | Short description       |                   |                |            |           |             |               |
      | short_description | fr_fr              | Courte description      |                   |                |            |           |             |               |
      | description       | Admin              | description             |                   | Text Area      | Store View | No        | Yes         | Yes           |
      | description       | Default Store View | Description             |                   |                |            |           |             |               |
      | description       | fr_fr              | Description             |                   |                |            |           |             |               |
      | price             | Admin              | price                   |                   | Price          |            | No        | No          | Yes           |
      | price             | Default Store View | Price                   |                   |                |            |           |             |               |
      | price             | fr_fr              | Prix                    |                   |                |            |           |             |               |
      | weight            | Admin              | weight                  |                   | Text Field     | Store View | No        | No          | Yes           |
      | weight            | Default Store View | Weight                  |                   |                |            |           |             |               |
      | weight            | fr_fr              | Poids                   |                   |                |            |           |             |               |
      | tax_class_id      | Admin              | tax_class_id            |                   | Dropdown       | Global     | No        | No          | Yes           |
      | tax_class_id      | Default Store View | Tax class id            |                   |                |            |           |             |               |
      | tax_class_id      | fr_fr              | Id de la classe de taxe |                   |                |            |           |             |               |
