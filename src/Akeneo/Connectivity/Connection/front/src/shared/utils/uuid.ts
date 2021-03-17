const uuid = (): string => {
    let date = new Date().getTime();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char: string) => {
        // eslint-disable-next-line no-bitwise
        const random = (date + Math.random() * 16) % 16 | 0;
        date = Math.floor(date / 16);

        // eslint-disable-next-line no-bitwise
        return ('x' === char ? random : (random & 0x3) | 0x8).toString(16);
    });
};

export default uuid;
