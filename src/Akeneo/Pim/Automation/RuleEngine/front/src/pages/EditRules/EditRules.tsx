import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";
import { useBackboneRouter } from "../../dependenciesTools/hooks";
import { useInitEditRules } from "../EditRules";
import { EditRulesContent } from "./EditRulesContent";

type Props = {
  ruleDefinitionCode: string;
};

const EditRules: React.FC<Props> = ({ ruleDefinitionCode }) => {
  const router = useBackboneRouter();
  const { error, ruleDefinition, locales } = useInitEditRules(
    ruleDefinitionCode,
    router
  );
  return (
    <ThemeProvider theme={akeneoTheme}>
      {error.status ? (
        "There was an error (TODO: better display)"
      ) : !ruleDefinition || !locales ? (
        "Loading (TODO: better display)"
      ) : (
        <EditRulesContent
          ruleDefinitionCode={ruleDefinitionCode}
          ruleDefinition={ruleDefinition}
          locales={locales}
        />
      )}
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
