export default (level: string, message: string, parameters: {[key: string]: string} = {}) => {
  return {type: 'NOTIFY', level, message, parameters};
};
