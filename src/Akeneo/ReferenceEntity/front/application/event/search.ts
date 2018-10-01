export const startLoading = () => {
  return {type: 'START_LOADING_RESULTS'};
};

export const stopLoading = () => {
  return {type: 'STOP_LOADING_RESULTS'};
};

export const goNextPage = () => {
  return {type: 'GO_NEXT_PAGE'};
};

export const goFirstPage = () => {
  return {type: 'GO_FIRST_PAGE'};
};
