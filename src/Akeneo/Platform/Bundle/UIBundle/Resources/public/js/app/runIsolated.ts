/**
 * Runs legacy code called from a React effect: an error it throws is reported in the console instead of unmounting the
 * whole application (React removes its tree on an error thrown in an effect). It is what the former Backbone
 * application did, since it was rendered from jQuery promise callbacks, which catch errors.
 */
const runIsolated = (callback: () => unknown): void => {
  try {
    callback();
  } catch (error) {
    console.error(error);
  }
};

export {runIsolated};
