export interface ConfirmDeleteState {
  isActive: boolean;
}
export default (
  state: ConfirmDeleteState = {isActive: false},
  action: {type: string; isActive: boolean}
): ConfirmDeleteState => {
  switch (action.type) {
    case 'START_DELETE_MODAL':
      state = {...state, isActive: true};
      break;
    case 'CONFIRM_DELETE_MODAL':
      state = {...state, isActive: false};
      break;
    case 'CANCEL_DELETE_MODAL':
      state = {...state, isActive: false};
      break;
    default:
      break;
  }

  return state;
};
