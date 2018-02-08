Feature: Import channels
  In order to setup my application
  As an administrator
  I need to be able to import channels

  Scenario: Successfully import new channel in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree;conversion_units
      site;Site;USD,EUR;de_DE,en_US,hy_AM;2014_collection;"weight: GRAM, length: MILLIMETER, volume: LITER"
      """
    When I import it via the job "csv_footwear_channel_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following channels:
      | code | label-en_US | currencies | locales           | tree            | conversion_units                           |
      | site | Site        | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection | weight:GRAM,length:MILLIMETER,volume:LITER |

  @jira https://akeneo.atlassian.net/browse/PIM-6041
  Scenario: Successfully import channel do not create empty conversion unit
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree;conversion_units
      mobile;Mobile app;EUR,USD;en_US,fr_FR;2014_collection;
      """
    When I import it via the job "csv_footwear_channel_import" as "Julia"
    And I wait for this job to finish
    And I launch the import job
    And I wait for the "csv_footwear_channel_import" job to finish
    When I import it via the job "csv_footwear_channel_export" as "Julia"
    And I wait for this job to finish
    And exported file of "csv_footwear_channel_export" should contain:
    """
    code;label-fr_FR;label-en_US;label-de_DE;conversion_units;currencies;locales;tree
    mobile;Mobile;Mobile;Mobil;;USD,EUR;en_US,fr_FR;2014_collection
    tablet;Tablette;Tablet;Tablet;;USD,EUR;en_US;2014_collection
    """

  Scenario: Successfully update existing channel and add a new one
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree
      site;Site;USD,EUR;de_DE,en_US,fr_FR;2014_collection
      mobile;Mobile app;EUR,USD;en_US,fr_FR;2014_collection
      """
    When I import it via the job "csv_footwear_channel_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following channels:
      | code   | label-en_US | currencies | locales           | tree            | conversion_units |
      | site   | Site        | EUR,USD    | de_DE,en_US,fr_FR | 2014_collection |                  |
      | mobile | Mobile app  | EUR,USD    | en_US,fr_FR       | 2014_collection |                  |

  Scenario: Successfully remove locales and currencies
    Given the "apparel" catalog configuration
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | import | csv_channel_import | csv_channel_import | CSV channel import |
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree
      print;Print;USD;en_US;2015_collection
      """
    When I import it via the job "csv_channel_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following channels:
      | code  | label-en_US | currencies | locales | tree            | conversion_units |
      | print | Print       | USD        | en_US   | 2015_collection |                  |
