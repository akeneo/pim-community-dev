const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');

export const redirectToAttributeGridFilteredByFamilyAndQuality = (familyId: number) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyId}&f[quality][value]=to_improve&t=attribute-grid`;
  DatagridState.set('attribute-grid', {
    filters: gridFilters,
  });

  window.location.href = '#' + Router.generate('pim_enrich_attribute_index');
};

export const redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes = (familyId: number) => {
  const gridFilters = `i=1&p=25&s[label]=-1&f[family][value][]=${familyId}&f[quality][value]=to_improve&t=attribute-grid&f[type][value][]=pim_catalog_multiselect&f[type][value][]=pim_catalog_simpleselect`;
  DatagridState.set('attribute-grid', {
    filters: gridFilters,
  });

  window.location.href = '#' + Router.generate('pim_enrich_attribute_index');
};
