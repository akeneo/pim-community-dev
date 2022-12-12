const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type DecimalSeparator = keyof typeof availableDecimalSeparators;

const isDecimalSeparator = (separator: any): separator is DecimalSeparator => separator in availableDecimalSeparators;

export type {DecimalSeparator};
export {availableDecimalSeparators, isDecimalSeparator};
