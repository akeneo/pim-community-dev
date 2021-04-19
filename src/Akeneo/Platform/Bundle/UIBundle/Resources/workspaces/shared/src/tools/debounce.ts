export const debounceCallback = (callback: (...args: any[]) => any, delay: number) => {
  let timer: number;

  return (...args: any[]) => {
    const context = this;

    clearTimeout(timer);
    timer = window.setTimeout(() => {
      callback.apply(context, args);
    }, delay);
  };
};
