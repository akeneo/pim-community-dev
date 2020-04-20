import {Translate} from "../../dependenciesTools";
import {Locale} from "../../models/Locale";

type ConditionLineProps = {
  register: any,
  lineNumber: number,
  translate: Translate,
  activatedLocales: Locale[],
}

export { ConditionLineProps }
