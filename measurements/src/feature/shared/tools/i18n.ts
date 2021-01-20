const getLabel = (labels: {[locale: string]: string}, locale: string, fallback: string): string => {
  return labels && labels[locale] ? labels[locale] : `[${fallback}]`;
};

export {getLabel};
