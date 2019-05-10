export const gridStateStoragePath = 'pim_reference_entity.record.grid.filter';

export default () => (store: any) => (next: any) => (action: any) => {
  if (
    (('LOCALE_CHANGED' === action.type && action.target === 'catalog') || 'CHANNEL_CHANGED' === action.type) &&
    'product' === store.getState().sidebar.currentTab
  ) {
  }

  return next(action);
};
