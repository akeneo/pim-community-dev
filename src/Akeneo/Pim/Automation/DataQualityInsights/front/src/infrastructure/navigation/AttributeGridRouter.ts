const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');

export const redirectToAttributeGridFilteredByFamilyAndQuality = (familyCode: string) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyCode}&f[quality][value]=to_improve&t=attribute-grid`;
  redirectToFilteredAttributeGrid(gridFilters);
};

export const redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes = (familyCode: string) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyCode}&f[quality][value]=to_improve&t=attribute-grid&f[type][value][]=pim_catalog_multiselect&f[type][value][]=pim_catalog_simpleselect`;
  redirectToFilteredAttributeGrid(gridFilters);
};

export const redirectToFilteredAttributeGrid = (gridFilters: string) => {
  DatagridState.set('attribute-grid', {
    filters: gridFilters,
  });

  window.location.href = '#' + Router.generate('pim_enrich_attribute_index');
};
