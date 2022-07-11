const alphabet = 'abcdefghijklmnopqrstuvwxyz';

export const indexify = <T>(items: T[]): {[key: string]: T} =>
    items.reduce(
        (list, item, index) => ({
            ...list,
            [alphabet.charAt(index)]: item,
        }),
        {}
    );
