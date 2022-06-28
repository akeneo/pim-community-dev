// It transforms an array to an object indexed with a random string.
export const indexify = <T>(items: T[]): {[key: string]: T} =>
    items.reduce(
        (list, item) => ({
            ...list,
            [(Math.random() + 1).toString(36).substring(7)]: item,
        }),
        {}
    );
