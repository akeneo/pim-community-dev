export default () => (store: any) => (next: any) => (action: any) => {
  if ('GRID_UPDATE_FILTER' === action.type) {
    sessionStorage.setItem(
      `pim_reference_entity.record.grid.search.${store.getState().form.data.identifier}`,
      action.value
    );
  }

  return next(action);
};
