export const postSave = () => {
  return {type: 'POST_SAVE'};
};

export const failSave = (response: any) => {
  return {type: 'FAIL_SAVE', response};
};
