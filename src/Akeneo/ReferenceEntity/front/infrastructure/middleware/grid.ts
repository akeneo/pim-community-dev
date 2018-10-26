export default () => (store: any) => (next: any) => (action: any) => {
  if ('GRID_STATUS_UPDATED' === action.type) {
    sessionStorage.setItem(
      `pim_reference_entity.record.grid.search.${store.getState().form.data.identifier}`,
      JSON.stringify(store.getState().grid.query.filters)
    );
  }

  return next(action);
};
