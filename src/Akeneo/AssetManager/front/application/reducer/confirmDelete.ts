export interface ConfirmDeleteState {
  isActive: boolean;
  identifier?: string;
  label?: string;
}
export default (
  state: ConfirmDeleteState = {isActive: false, identifier: undefined, label: undefined},
  action: {type: string; identifier?: string; label?: string}
): ConfirmDeleteState => {
  switch (action.type) {
    case 'DELETE_MODAL_OPEN':
      state = {...state, isActive: true, identifier: action.identifier, label: action.label};
      break;
    case 'DELETE_MODAL_CLOSE':
    case 'DISMISS':
    case 'DELETE_MODAL_CANCEL':
      state = {...state, isActive: false, identifier: undefined, label: undefined};
      break;
    default:
      break;
  }

  return state;
};
