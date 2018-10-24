export default () => (store: any) => (next: any) => (action: any) => {
  if ('GRID_UPDATE_FILTER' === action.type) {
    sessionStorage.setItem(`search-${store.getState().form.data.identifier}`, action.value);
  }

  return next(action);
};
