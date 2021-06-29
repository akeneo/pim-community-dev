const debounce = (callback: (...args: any[]) => any, delay: number) => {
    let timer: number;

    return (...args: any[]) => {
        if (timer) {
            window.clearTimeout(timer);
        }

        timer = window.setTimeout(() => callback(...args), delay);
    };
};

export default debounce;
