Feature: Allow only XHR requests for some job profiles actions
  In order to protect job profiles from CSRF attacks
  As a developer
  I need to only do XHR calls for some job profiles actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for export job profiles deletion
    When I make a direct authenticated DELETE call on the "csv_footwear_association_type_export" export job profile
    Then there should be a "csv_footwear_association_type_export" export job profile

  Scenario: Authorize only XHR calls for import job profiles deletion
    When I make a direct authenticated DELETE call on the "csv_footwear_association_type_import" import job profile
    Then there should be a "csv_footwear_association_type_import" import job profile
