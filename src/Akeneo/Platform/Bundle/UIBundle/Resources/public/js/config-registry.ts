const getConfig = <T>(configPath: string): T | null => {
  return __moduleConfig[configPath] ?? null;
}

export {getConfig}
