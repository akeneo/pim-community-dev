type ModuleConfig = typeof __moduleConfig;

const getConfig = <T>(configPath: keyof ModuleConfig): T | null => {
  return (__moduleConfig[configPath] as unknown as T) ?? null;
};

export {getConfig};
