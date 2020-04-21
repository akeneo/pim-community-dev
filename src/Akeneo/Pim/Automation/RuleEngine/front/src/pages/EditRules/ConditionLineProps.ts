import {Translate} from "../../dependenciesTools";
import {Locale} from "../../models";
import {Scope} from "../../models";

type ConditionLineProps = {
  register: any,
  lineNumber: number,
  translate: Translate,
  locales: Locale[],
  scopes: Scope[],
  currentCatalogLocale: string,
}

export { ConditionLineProps }
