const locales = [
  {id: 39, code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
  {id: 58, code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
  {id: 90, code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
];
const channel = {
  currencies: ['USD', 'EUR'],
  locales: locales,
  category_tree: 'master',
  conversion_units: [],
  meta: {
    created: '01/01/2015',
    form: '',
    id: Math.random(),
    updated: '02/01/2020',
  },
};
const mockedScopes = [
  {
    ...channel,
    code: 'ecommerce',
    labels: {en_US: 'Ecommerce', de_DE: 'Ecommerce', fr_FR: 'Ecommerce'},
  },
  {
    ...channel,
    code: 'mobile',
    labels: {en_US: 'Mobile', de_DE: 'Mobil', fr_FR: 'Mobile'},
  },
  {
    ...channel,
    code: 'print',
    labels: {en_US: 'Print', de_DE: 'Drucken', fr_FR: 'Impression'},
  },
];

export default mockedScopes;
