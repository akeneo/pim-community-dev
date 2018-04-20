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

  setUsername(username) {
    this.user.username = username;
    this.user.email = `${username}@example.com`;

    return this;
  }

  setEmail(email) {
    this.user.email = email;

    return this;
  }

  setFirstName(firstName) {
    this.user.firstName = firstName;

    return this;
  }

  setLastName(lastName) {
    this.user.lastName = lastName;

    return this;
  }

  setCatalogLocale(catalogLocale) {
    this.user.catalogLocale = catalogLocale;

    return this;
  }

  setUiLocale(uiLocale) {
    this.user.uiLocale = uiLocale;

    return this;
  }

  setCatalogScope(catalogScope) {
    this.user.catalogScope = catalogScope;

    return this;
  }

  setDefaultTree(defaultTree) {
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
