export const parseInputNumberValue = (value: string): number => {
    const parsed = value
        // remove when starting with a dot (not supported)
        .replace(/^\.+/g, '')
        // remove forbidden characters
        .replace(/[^0-9.]+/g, '')
        // keep only one dot
        .split('.')
        .slice(0, 2)
        .join('.');

    const number = Number(parsed);

    if (number.toString() === parsed) {
        return number;
    }

    return parsed as unknown as number;
};
