const DatagridState = require('pim/datagrid/state');

export const updateDatagridStateWithFilterOnAssetCode = (attributeCode: string, value: string) => {
  const filters = `f[${attributeCode}][value][]=${value}&f[${attributeCode}][type]=in`;
  DatagridState.set('product-grid', {
    filters: filters,
  });
};
