type ModuleConfig = typeof __moduleConfig;
const getConfig = (configPath: keyof ModuleConfig) => {
  return __moduleConfig[configPath] ?? null;
};

export {getConfig};
