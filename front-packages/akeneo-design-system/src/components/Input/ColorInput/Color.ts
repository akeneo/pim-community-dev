const convertColorToLongHexColor = (value: string): string => {
  if (!isValidShortHexColor(value)) return value;

  return `#${value[1]}${value[1]}${value[2]}${value[2]}${value[3]}${value[3]}`;
};

const isValidShortHexColor = (value: string): boolean => {
  return /^#[A-Fa-f0-9]{3}$/.test(value);
};

const isValidLongHexColor = (value: string): boolean => {
  return /^#[A-Fa-f0-9]{6}$/.test(value);
};

const isValidColor = (value: string): boolean => {
  return isValidLongHexColor(value) || isValidShortHexColor(value);
};

export {isValidColor, convertColorToLongHexColor};
