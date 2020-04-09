import { useApplicationContext } from "./useApplicationContext";
import { Translate } from "../provider/applicationDependenciesProvider.type";

const useTranslate = (
  id: string,
  placeholders: { [name: string]: string } = {},
  count: number = 1
): string => {
  const { translate } = useApplicationContext();
  if (translate) {
    return translate(id, placeholders, count);
  }
  console.warn(
    "[ApplicationContext]: Translate has not been properly initiated"
  );
  return "";
};

const useSimpleTranslate = (): Translate => {
  const { translate } = useApplicationContext();
  if (translate) {
    return translate;
  }
  throw new Error(
    "[ApplicationContext]: Translate has not been properly initiated"
  );
};

export { useTranslate, useSimpleTranslate };
