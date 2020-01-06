class UserBuilder {
  constructor() {
    this.user = {
      username: 'admin',
      email: 'admin@example.com',
      namePrefix: null,
      firstName: 'John',
      middleName: null,
      lastName: 'Doe',
      nameSuffix: null,
      image: null,
      lastLogin: 1518092814,
      loginCount: 18,
      catalog_default_locale: 'en_US',
      user_default_locale: 'en_US',
      catalog_default_scope: 'ecommerce',
      default_category_tree: 'master',
      email_notifications: null,
      display_proposals_state_notifications: null,
      proposals_state_notifications: null,
      display_proposals_to_review_notification: null,
      proposals_to_review_notification: null,
      avatar: {
        filePath: ''
      },
      meta: {
          id: 1
      },
      ui_locale_decimal_separator: '.'
    }
  }

  withUsername(username) {
    this.user.username = username;
    this.user.email = `${username}@example.com`;

    return this;
  }

  withEmail(email) {
    this.user.email = email;

    return this;
  }

  withFirstName(firstName) {
    this.user.firstName = firstName;

    return this;
  }

  withLastName(lastName) {
    this.user.lastName = lastName;

    return this;
  }

  withCatalogLocale(catalogLocale) {
    this.user.catalogLocale = catalogLocale;

    return this;
  }

  withUiLocale(uiLocale) {
    this.user.uiLocale = uiLocale;

    return this;
  }

  withCatalogScope(catalogScope) {
    this.user.catalogScope = catalogScope;

    return this;
  }

  withDefaultTree(defaultTree) {
    this.user.defaultTree = defaultTree;

    return this;
  }

  build() {
    return this.user;
  }
}

/**
 * @returns {Object}
 */
module.exports = UserBuilder;
