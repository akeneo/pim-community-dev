export const openDeleteModal = (identifier?: string, label?: string) => {
  return {type: 'DELETE_MODAL_OPEN', identifier, label};
};

export const closeDeleteModal = () => {
  return {type: 'DELETE_MODAL_CLOSE'};
};

export const cancelDeleteModal = () => {
  return {type: 'DELETE_MODAL_CANCEL'};
};
