import { useContext } from "react";
import {
  ApplicationDependenciesContextProps,
  ApplicationDependenciesContext
} from "../provider/ApplicationDependenciesProvider";

const useApplicationContext = () => {
  const context = useContext<ApplicationDependenciesContextProps>(
    ApplicationDependenciesContext
  );
  if (!context) {
    throw new Error(
      "[ApplicationContext]: You are trying to use 'useApplicationContext' outside ApplicationProvider"
    );
  }
  return context;
};

export { useApplicationContext };
