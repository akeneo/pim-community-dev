const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_attribute_option_check_text_ignore';

const fetchIgnoreOptionIssue = async (word: string, locale: string, attributeCode: string, optionCode: string) => {
  await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: `word=${encodeURIComponent(word)}&locale=${encodeURIComponent(locale)}&attribute_code=${encodeURIComponent(
      attributeCode
    )}&option_code=${encodeURIComponent(optionCode)}`,
  });

  return [];
};

export default fetchIgnoreOptionIssue;
