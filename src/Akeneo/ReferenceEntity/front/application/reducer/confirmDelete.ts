export interface ConfirmDeleteState {
  isActive: boolean;
}
export default (state: ConfirmDeleteState = {isActive: false}, action: {type: string}): ConfirmDeleteState => {
  switch (action.type) {
    case 'DELETE_MODAL_OPEN':
      state = {...state, isActive: true};
      break;
    case 'DELETE_MODAL_CLOSE':
    case 'DISMISS':
    case 'DELETE_MODAL_CANCEL':
      state = {...state, isActive: false};
      break;
    default:
      break;
  }

  return state;
};
