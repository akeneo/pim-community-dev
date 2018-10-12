export const startDeleteModal =  () => {
  return {type: 'START_DELETE_MODAL', active: true}
}

export const confirmDeleteModal =  () => {
  return {type: 'CONFIRM_DELETE_MODAL', active: false}
}

export const cancelDeleteModal =  () => {
  return {type: 'CANCEL_DELETE_MODAL', active: false}
}
