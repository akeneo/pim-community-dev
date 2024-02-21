const SecurityContext = jest.fn();
SecurityContext.isGranted = jest.fn();

module.exports = SecurityContext;
