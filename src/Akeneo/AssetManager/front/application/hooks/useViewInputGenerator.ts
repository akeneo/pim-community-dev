import EditionValue from "../../domain/model/asset/edition-value";
import {useValueConfig} from "./useValueConfig";
import {getFieldView} from "../configuration/value";

const useViewInputGenerator = () => {
  const valueConfig = useValueConfig();

  return (value: EditionValue) => getFieldView(valueConfig, value);
};

export {useViewInputGenerator};
