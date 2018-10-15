export const openDeleteModal = () => {
  return {type: 'DELETE_MODAL_OPEN'};
};

export const closeDeleteModal = () => {
  return {type: 'DELETE_MODAL_CLOSE'};
};

export const cancelDeleteModal = () => {
  return {type: 'DELETE_MODAL_CANCEL'};
};
