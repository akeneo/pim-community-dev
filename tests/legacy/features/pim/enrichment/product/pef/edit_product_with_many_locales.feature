@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "apparel" catalog configuration
    And I add the "english" locale to the "ecommerce" channel
    And I add the "english UK" locale to the "ecommerce" channel
    And I add the "french" locale to the "ecommerce" channel
    And I add the "german" locale to the "ecommerce" channel
    And I add the "spanish" locale to the "ecommerce" channel
    And I add the "italian" locale to the "ecommerce" channel
    And I add the "portuguese" locale to the "ecommerce" channel
    And I add the "russian" locale to the "ecommerce" channel
    And I add the "japanese" locale to the "ecommerce" channel
    And I add the "english" locale to the "print" channel
    And I add the "english UK" locale to the "print" channel
    And I add the "french" locale to the "print" channel
    And I add the "german" locale to the "print" channel
    And I add the "spanish" locale to the "print" channel
    And I add the "italian" locale to the "print" channel
    And I add the "portuguese" locale to the "print" channel
    And I add the "russian" locale to the "print" channel
    And I add the "japanese" locale to the "print" channel
    And I add the "english" locale to the "tablet" channel
    And I add the "english UK" locale to the "tablet" channel
    And I add the "french" locale to the "tablet" channel
    And I add the "german" locale to the "tablet" channel
    And I add the "spanish" locale to the "tablet" channel
    And I add the "italian" locale to the "tablet" channel
    And I add the "portuguese" locale to the "tablet" channel
    And I add the "russian" locale to the "tablet" channel
    And I add the "japanese" locale to the "tablet" channel
    And the following products:
      | sku          | family  |
      | tshirt-white | tshirts |
    And the following attribute groups:
      | code       | label-en_US      | label-en_GB      | label-en_GB      | label-fr_FR      | label-de_DE      | label-es_ES      | label-it_IT      | label-pt_PT      | label-ru_RU      | label-ja_JP      |
      | general    | general-en_US    | general-en_GB    | general-en_GB    | general-fr_FR    | general-de_DE    | general-es_ES    | general-it_IT    | general-pt_PT    | general-ru_RU    | general-ja_JP    |
      | media      | media-en_US      | media-en_GB      | media-en_GB      | media-fr_FR      | media-de_DE      | media-es_ES      | media-it_IT      | media-pt_PT      | media-ru_RU      | media-ja_JP      |
      | sales      | sales-en_US      | sales-en_GB      | sales-en_GB      | sales-fr_FR      | sales-de_DE      | sales-es_ES      | sales-it_IT      | sales-pt_PT      | sales-ru_RU      | sales-ja_JP      |
      | sizes      | sizes-en_US      | sizes-en_GB      | sizes-en_GB      | sizes-fr_FR      | sizes-de_DE      | sizes-es_ES      | sizes-it_IT      | sizes-pt_PT      | sizes-ru_RU      | sizes-ja_JP      |
      | colors     | colors-en_US     | colors-en_GB     | colors-en_GB     | colors-fr_FR     | colors-de_DE     | colors-es_ES     | colors-it_IT     | colors-pt_PT     | colors-ru_RU     | colors-ja_JP     |
      | additional | additional-en_US | additional-en_GB | additional-en_GB | additional-fr_FR | additional-de_DE | additional-es_ES | additional-it_IT | additional-pt_PT | additional-ru_RU | additional-ja_JP |
      | internal   | internal-en_US   | internal-en_GB   | internal-en_GB   | internal-fr_FR   | internal-de_DE   | internal-es_ES   | internal-it_IT   | internal-pt_PT   | internal-ru_RU   | internal-ja_JP   |
    And the following product values:
      | product      | attribute              | value                                   | locale | scope     |
      | tshirt-white | name                   | White t-shirt                           | en_US  |           |
      | tshirt-white | name                   | White t-shirt                           | en_GB  |           |
      | tshirt-white | name                   | T-shirt blanc                           | fr_FR  |           |
      | tshirt-white | name                   | Weißes T-Shirt                          | de_DE  |           |
      | tshirt-white | description            | A stylish white t-shirt                 | en_US  | ecommerce |
      | tshirt-white | description            | An elegant white t-shirt                | en_GB  | ecommerce |
      | tshirt-white | description            | Un T-shirt blanc élégant                | fr_FR  | ecommerce |
      | tshirt-white | description            | Ein elegantes weißes T-Shirt            | de_DE  | ecommerce |
      | tshirt-white | description            | My awesome description for ecommerce ES | es_ES  | ecommerce |
      | tshirt-white | description            | My awesome description for ecommerce IT | it_IT  | ecommerce |
      | tshirt-white | description            | My awesome description for ecommerce PT | pt_PT  | ecommerce |
      | tshirt-white | description            | My awesome description for ecommerce RU | ru_RU  | ecommerce |
      | tshirt-white | description            | My awesome description for ecommerce JP | ja_JP  | ecommerce |
      | tshirt-white | description            | A stylish white t-shirt                 | en_US  | print     |
      | tshirt-white | description            | An elegant white t-shirt                | en_GB  | print     |
      | tshirt-white | description            | Un T-shirt blanc élégant                | fr_FR  | print     |
      | tshirt-white | description            | Ein elegantes weißes T-Shirt            | de_DE  | print     |
      | tshirt-white | description            | My awesome description for print ES     | es_ES  | print     |
      | tshirt-white | description            | My awesome description for print IT     | it_IT  | print     |
      | tshirt-white | description            | My awesome description for print PT     | pt_PT  | print     |
      | tshirt-white | description            | My awesome description for print RU     | ru_RU  | print     |
      | tshirt-white | description            | My awesome description for print JP     | ja_JP  | print     |
      | tshirt-white | description            | A stylish white t-shirt                 | en_US  | tablet    |
      | tshirt-white | description            | An elegant white t-shirt                | en_GB  | tablet    |
      | tshirt-white | description            | Un T-shirt blanc élégant                | fr_FR  | tablet    |
      | tshirt-white | description            | Ein elegantes weißes T-Shirt            | de_DE  | tablet    |
      | tshirt-white | description            | My awesome description for tablet ES    | es_ES  | tablet    |
      | tshirt-white | description            | My awesome description for tablet IT    | it_IT  | tablet    |
      | tshirt-white | description            | My awesome description for tablet PT    | pt_PT  | tablet    |
      | tshirt-white | description            | My awesome description for tablet RU    | ru_RU  | tablet    |
      | tshirt-white | description            | My awesome description for tablet JP    | ja_JP  | tablet    |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | en_US  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | en_GB  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | fr_FR  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | de_DE  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | es_ES  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | it_IT  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | pt_PT  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | ru_RU  |           |
      | tshirt-white | legend                 | My awesome legend for ecommerce JP      | ja_JP  |           |
      | tshirt-white | price                  | 12 EUR,15 USD, 5 GBP                    |        |           |
      | tshirt-white | customer_rating        | 1                                       |        | ecommerce |
      | tshirt-white | customer_rating        | 3                                       |        | print     |
      | tshirt-white | customer_rating        | 2                                       |        | tablet    |
      | tshirt-white | release_date           | 2015-01-01                              |        | ecommerce |
      | tshirt-white | size                   | size_XS                                 |        |           |
      | tshirt-white | color                  | white                                   |        |           |
      | tshirt-white | additional_colors      | additional_black                        |        |           |
      | tshirt-white | manufacturer           | american_apparel                        |        |           |
      | tshirt-white | country_of_manufacture | usa                                     |        |           |
      | tshirt-white | handmade               | 1                                       |        |           |
      | tshirt-white | washing_temperature    | 40 CELSIUS                              |        |           |

  @jira https://akeneo.atlassian.net/browse/PIM-4748
  Scenario: Successfully create, edit and save a product with many locales
    Given I am logged in as "Mary"
    And I am on the "tshirt-white" product page
    And I fill in the following information:
      | Name | My T |
    When I press the "Save" button
    Then I should be on the product "tshirt-white" edit page
    Then the product Name should be "My T"
