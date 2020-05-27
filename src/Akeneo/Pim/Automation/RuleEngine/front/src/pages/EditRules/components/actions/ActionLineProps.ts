import { Router, Translate } from '../../../../dependenciesTools';
import { LocaleCode } from "../../../../models";

type ActionLineProps = {
  lineNumber: number;
  translate: Translate;
  handleDelete: () => void;
  router: Router;
  currentCatalogLocale: LocaleCode;
};

export { ActionLineProps };
