export interface UploadState {
  active: boolean;
}

const initUploadState = (): UploadState => ({
  active: false,
});

export default (state: UploadState = initUploadState(), action: {type: string}) => {
  switch (action.type) {
    case 'ASSET_UPLOAD_START':
      state = {...initUploadState(), active: true};
      break;

    case 'DISMISS':
    case 'ASSET_UPLOAD_DONE':
      state = {
        ...state,
        active: false,
      };
      break;

    default:
  }

  return state;
};
