const flagTemplate = (country: string, language: string, displayLanguage: boolean) => {
  return `
<span class="flag-language">
  <i class="flag flag-${country}"></i>${
    displayLanguage
      ? `
  <span class="language">${language}</span>`
      : ''
  }
</span>`;
};

export const getFlag = (locale: string, displayLanguage: boolean = true): string => {
  if (!locale) {
    return '';
  }

  var country = '';
  var language = locale;

  if (locale.includes('_')) {
    const info = locale.split('_');
    language = info[0];
    country = info[1];

    if (3 === info.length) {
      country = info[2];
    }
  }

  return flagTemplate(country.toLowerCase(), language, displayLanguage);
};

export const getLabel = (labels: {[locale: string]: string}, locale: string, fallback: string): string => {
  return (labels && labels[locale]) ? labels[locale] : `[${fallback}]`;
};
