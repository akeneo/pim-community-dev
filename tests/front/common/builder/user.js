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
      birthday: null,
      image: null,
      lastLogin: 1518092814,
      loginCount: 18,
      catalogLocale: 'en_US',
      uiLocale: 'en_US',
      catalogScope: 'ecommerce',
      defaultTree: 'master',
      avatar: null,
      meta: {
          id: 1
      }
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
