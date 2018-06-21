export interface EditFormState {
  originalData: string;
  isDirty: boolean;
}

export default (path: string, updateType: string, receivedType: string) => {
  return (
    state: EditFormState = {originalData: '', isDirty: false},
    action: {type: string; [key:string]: any}
  ): EditFormState => {
    switch (action.type) {
      case updateType:
        state = {...state, isDirty: state.originalData !== JSON.stringify(action[path])};
        break;
      case receivedType:
        state = {...state, originalData: JSON.stringify(action[path]), isDirty: false};
        break;
      default:
        break;
    }

    return state;
  };
};
