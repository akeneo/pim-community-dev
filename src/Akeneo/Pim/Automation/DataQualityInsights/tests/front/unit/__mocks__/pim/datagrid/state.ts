const DatagridState = jest.fn();
DatagridState.set = jest.fn();
DatagridState.get = jest.fn(
  () => 'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success'
);

module.exports = DatagridState;
