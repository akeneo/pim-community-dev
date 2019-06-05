export const accessProperty = (data: any, path: string, defaultValue: any = null): any => {
  const pathPart = path.split('.');

  const part = pathPart[0].replace(/__DOT__/g, '.');
  if (undefined === data[part]) {
    return defaultValue;
  }

  return 1 === pathPart.length ? data[part] : accessProperty(data[part], pathPart.slice(1).join('.'), defaultValue);
};
