@javascript
Feature: Export media with products
  In order to re-use the images and documents I have setted on my products
  As Julia
  I need to be able to export them along with the products

  Scenario: Successfully export media
    Given the following family:
      | code     |
      | funboard |
    Given the following product:
      | sku                 | family   |
      | bic-core-148        | funboard |
      | fanatic-freewave-76 | funboard |
    Given the following product attributes:
      | label       | type  |
      | Name        | text  |
      | Front view  | image |
      | User manual | file  |
    And the following product values:
      | product             | attribute   | value                   |
      | bic-core-148        | Name        | Bic Core 148            |
      | bic-core-148        | Front view  | bic-core-148.gif        |
      | bic-core-148        | User manual | bic-core-148.txt        |
      | fanatic-freewave-76 | Name        | Fanatic Freewave 76     |
      | fanatic-freewave-76 | Front view  | fanatic-freewave-76.gif |
      | fanatic-freewave-76 | User manual | fanatic-freewave-76.txt |
    And the following category:
      | code  | label | parent  | products                          |
      | sport | Sport | default | bic-core-148, fanatic-freewave-76 |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_export | acme_product_export | Product export for Acme.com | export |
    And the following job "acme_product_export" configuration:
      | element   | property      | value               |
      | reader    | channel       | ecommerce           |
      | processor | delimiter     | ;                   |
      | processor | enclosure     | "                   |
      | processor | withHeader    | yes                 |
      | writer    | directoryName | /tmp/product_export |
      | writer    | fileName      | product_export.csv  |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    And I am on the "acme_product_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then exported file of "acme_product_export" should contain:
    """
    sku;family;groups;categories;frontView;name;userManual
    bic-core-148;funboard;;sport;files/bic-core-148/frontView/bic-core-148.gif;"Bic Core 148";files/bic-core-148/userManual/bic-core-148.txt
    fanatic-freewave-76;funboard;;sport;files/fanatic-freewave-76/frontView/fanatic-freewave-76.gif;"Fanatic Freewave 76";files/fanatic-freewave-76/userManual/fanatic-freewave-76.txt

    """
    Then export directory of "acme_product_export" should contain the following media:
      | files/bic-core-148/frontView/bic-core-148.gif                |
      | files/bic-core-148/userManual/bic-core-148.txt               |
      | files/fanatic-freewave-76/frontView/fanatic-freewave-76.gif  |
      | files/fanatic-freewave-76/userManual/fanatic-freewave-76.txt |
