export default (error: any) => {
  if (500 === error.status) {
    throw new Error('Internal Server error');
  }
  if (0 === error.status) {
    throw new Error('Client is offline');
  }
  if (400 === error.status) {
    return error.responseJSON;
  }

  throw new Error(error.responseJSON);
};
