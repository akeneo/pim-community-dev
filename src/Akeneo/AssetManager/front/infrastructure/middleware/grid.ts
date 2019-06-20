export const gridStateStoragePath = 'pim_asset_manager.asset.grid.filter';

export default () => (store: any) => (next: any) => (action: any) => {
  if ('GRID_STATE_UPDATED' === action.type) {
    sessionStorage.setItem(
      `${gridStateStoragePath}.${store.getState().form.data.identifier}`,
      JSON.stringify(store.getState().grid.query.filters)
    );
  }

  return next(action);
};
