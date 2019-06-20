export default (promise: any): Promise<any> => {
  return new Promise((resolve, reject) => {
    promise.then(resolve).fail(reject);
  });
};
