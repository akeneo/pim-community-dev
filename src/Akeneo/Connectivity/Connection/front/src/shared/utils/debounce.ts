const debounce = (callback: (...args: any[]) => any, delay: number) => {
    let timer: number;

    return (...args: any[]) => {
        const context = this;

        clearTimeout(timer);
        // @ts-ignore
        timer = setTimeout(() => {
            callback.apply(context, args);
        }, delay);
    };
};

export default debounce;
