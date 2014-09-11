@javascript
Feature: Magento attribute export
  In order to view attributes in Magento
  As an Administrator
  I need to be able to export attributes in Magento

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
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_attribute_export" job to finish
    When I am on the "magento_option_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_option_export" job to finish
    Then I check if "attributes" were sent in Magento
