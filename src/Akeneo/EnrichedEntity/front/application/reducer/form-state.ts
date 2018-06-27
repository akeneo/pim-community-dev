export interface FormState {
  originalData: string;
  isDirty: boolean;
}

export default (path: string, updateType: string, receivedType: string) => {
  return (
    state: FormState = {originalData: '', isDirty: false},
    action: {type: string; [key:string]: any}
  ): FormState => {
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
