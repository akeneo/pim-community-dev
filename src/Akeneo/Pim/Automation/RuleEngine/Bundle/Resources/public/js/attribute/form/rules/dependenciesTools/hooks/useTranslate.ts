import { useApplicationContext } from "./useApplicationContext";

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

export { useTranslate };
