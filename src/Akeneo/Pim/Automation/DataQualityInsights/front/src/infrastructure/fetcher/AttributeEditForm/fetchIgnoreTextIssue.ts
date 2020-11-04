const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_attribute_check_text_ignore';

const fetchIgnoreTextIssue = async (word: string, locale: string, attributeCode: string) => {
  await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `word=${encodeURIComponent(word)}&locale=${encodeURIComponent(locale)}&attribute_code=${encodeURIComponent(
      attributeCode
    )}`,
  });

  return [];
};

export default fetchIgnoreTextIssue;
