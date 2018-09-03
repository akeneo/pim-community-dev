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

  const info = locale.split('_');
  let language = info[0];
  let country = info[1];

  if (3 === info.length) {
    country = info[2];
  }

  return flagTemplate(country.toLowerCase(), language, displayLanguage);
};

export const getLabel = (labels: {[locale: string]: string}, locale: string, fallback: string): string => {
  return labels[locale] ? labels[locale] : `[${fallback}]`;
};
