export interface UserState {
  catalogLocale?: string;
  catalogChannel?: string;
  uiLocale?: string;
}

export default (
  state: UserState = {},
  action: {type: string; target: string; locale?: string; channel?: string}
): UserState => {
  switch (action.type) {
    case 'LOCALE_CHANGED':
      state = {...state, [`${action.target}Locale`]: action.locale};
      break;
    case 'CHANNEL_CHANGED':
      state = {...state, [`${action.target}Channel`]: action.channel};
      break;
    default:
      break;
  }

  return state;
};
