const i18n = jest.fn();
i18n.getLabel = jest.fn((labels: {[locale: string]: string}, locale: string, fallback: string) => `[${fallback}]`);

module.exports = i18n;
