import {redirectToFilteredAttributeGrid} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/navigation/AttributeGridRouter';

const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');

export const redirectToAttributeGridFilteredByFamilyAndQuality = (familyCode: string, locale: string) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyCode}&f[quality][value]=to_improve&t=attribute-grid&f[quality][value]=${locale}`;
  DatagridState.set('attribute-grid', {
    filters: gridFilters,
  });

  window.location.href = '#' + Router.generate('pim_enrich_attribute_index');
};

export const redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes = (
  familyCode: string,
  locale: string
) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyCode}&f[quality][value]=to_improve&t=attribute-grid&f[type][value][]=pim_catalog_multiselect&f[type][value][]=pim_catalog_simpleselect&f[quality][value]=${locale}`;
  DatagridState.set('attribute-grid', {
    filters: gridFilters,
  });

  window.location.href = '#' + Router.generate('pim_enrich_attribute_index');
};

export const redirectToAttributeGridFilteredByKeyIndicator = (keyIndicator: string) => {
  redirectToFilteredAttributeGrid(`s[label]=-1&f[quality][value]=${keyIndicator}&t=attribute-grid`);
};

export const redirectToAttributeGridFilteredByFamilyAndKeyIndicator = (familyIds: string[], keyIndicator: string) => {
  const familyFilter = familyIds.map((familyId: string) => `f[family][value][]=${familyId}`).join('&');
  redirectToFilteredAttributeGrid(`s[label]=-1&f[quality][value]=${keyIndicator}&t=attribute-grid&${familyFilter}`);
};
