const flagTemplate = (country: string, language: string, displayLanguage: boolean) => {
  return `
<span class="flag-language">
  <i class="flag flag-${country}"></i>${displayLanguage ? `
  <span class="language">${language}</span>` : ''}
</span>`;
};

export const getFlag = (locale: string, displayLanguage: boolean = true): string => {
  if (!locale) {
    return '';
  }

  const [language, country] = locale.split('_');

  return flagTemplate(country.toLowerCase(), language, displayLanguage);
};

export const getLabel = (labels: {[locale: string]: string}, locale: string, fallback: string): string => {
  return labels[locale] ? labels[locale] : `[${fallback}]`;
};
