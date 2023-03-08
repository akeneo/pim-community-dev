const selectedOptionsMockResponse = [
  {code: 'option_a', labels: {en_US: 'Option A'}},
  {code: 'last_option', labels: {en_US: '[last_option]'}},
];

const firstPaginatedResponse = [...Array(20)].map((_, i) => ({code: `Option${i}`, labels: {}}));
const secondPaginatedResponse = [...Array(10)].map((_, i) => ({code: `Option${i + 20}`, labels: {}}));

export {selectedOptionsMockResponse, firstPaginatedResponse, secondPaginatedResponse};
