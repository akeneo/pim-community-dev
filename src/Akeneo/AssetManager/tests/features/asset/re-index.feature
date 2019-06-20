Feature: Reindex the assets
  In order to search through assets
  As a system administrator
  I want to re-index the assets of the Pim

  @acceptance-back @success
  Scenario: Re-index all the assets of a specific asset family
    Given the asset family "designers"
    And none of the assets of "designers" are indexed
    When the system administrator reindexes all the assets of "designers"
    Then the assets of the asset family "designers" have been indexed

  @acceptance-back @error
  Scenario: Cannot re-index assets with wrong asset family identifier
    When the system administrator reindexes the assets of an asset family that does not exist
    Then there should be a validation error with message 'The asset family "unknown_asset_family" was not found.'
