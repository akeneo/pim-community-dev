const UserContext = {
  get: (data: string) => {
    switch (data) {
      case 'username':
        return 'julia';
      case 'email':
        return 'julia@akeneo.com';
      case 'first_name':
        return 'julia';
      case 'last_name':
        return 'Stark';
      default:
        return data;
    }
  },
};

module.exports = UserContext;
