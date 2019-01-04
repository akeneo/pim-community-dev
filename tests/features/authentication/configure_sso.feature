Feature: Configure the SSO
  In order to use the SSO on the PIM according to my Identity Provider needs
  As an administrator user
  I want to configure it with valid SAML properties

  @acceptance-back
  Scenario: Accept a valid configuration
    Given a configuration
    Then I should have no validation errors

  @acceptance-back
  Scenario: Reject an empty configuration
    Given an empty configuration
    Then I should have the following validation errors:
      | path                              | message                         |
      | code                              | This value should not be blank. |
      | identityProviderEntityId          | This value should not be blank. |
      | identityProviderSignOnUrl         | This value should not be blank. |
      | identityProviderLogoutUrl         | This value should not be blank. |
      | identityProviderPublicCertificate | This value should not be blank. |
      | serviceProviderEntityId           | This value should not be blank. |
      | serviceProviderPublicCertificate  | This value should not be blank. |
      | serviceProviderPrivateCertificate | This value should not be blank. |


  @acceptance-back
  Scenario: Reject a configuration with invalid URLs
    Given a configuration with invalid URLs
    Then I should have the following validation errors:
      | path                      | message                        |
      | identityProviderEntityId  | This value is not a valid URL. |
      | identityProviderSignOnUrl | This value is not a valid URL. |
      | identityProviderLogoutUrl | This value is not a valid URL. |
      | serviceProviderEntityId   | This value is not a valid URL. |

  @acceptance-back
  Scenario: Reject a configuration with invalid certificates
    Given a configuration with invalid certificates
    Then I should have the following validation errors:
      | path                              | message                          |
      | identityProviderPublicCertificate | This is not a valid certificate. |
      | serviceProviderPublicCertificate  | This is not a valid certificate. |

  @acceptance-back
  Scenario: Reject a configuration with expired certificates
    Given a configuration with an expired IdP public certificate
    Then I should have the following validation errors:
      | path                              | message                       |
      | identityProviderPublicCertificate | This certificate has expired. |

  @acceptance-back
  Scenario: Reject a configuration with private certificate not matching the public certificate
    Given a configuration with invalid public and private key pair
    Then I should have the following validation errors:
      | path                              | message                                                      |
      | serviceProviderPublicCertificate  | Service Provider public and private certificates must match. |
      | serviceProviderPrivateCertificate | Service Provider public and private certificates must match. |

