export const debounceCallback = (callback: (...args: any[]) => any, delay: number) => {
    let timer: number;

    return (...args: any[]) => {
        // eslint-disable-next-line @typescript-eslint/no-this-alias
        const context = this;

        clearTimeout(timer);
        timer = setTimeout(() => {
            callback.apply(context, args);
        }, delay);
    };
};
