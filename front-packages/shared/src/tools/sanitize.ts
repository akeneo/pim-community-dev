const sanitize = (value: string): string => {
  const regex = /[a-zA-Z0-9_]/;

  return value
    .split('')
    .filter((char: string) => char !== ' ')
    .map((char: string) => (char.match(regex) ? char : '_'))
    .join('')
    .toLocaleLowerCase();
};

export {sanitize};
