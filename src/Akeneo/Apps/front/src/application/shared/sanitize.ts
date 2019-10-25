export const sanitize = (value: string) => {
    const regex = /[a-zA-Z0-9_]/;

    return value
        .split('')
        .filter((char: string) => char !== ' ')
        .map((char: string) => (regex.exec(char) ? char : '_'))
        .join('')
        .toLocaleLowerCase();
};
