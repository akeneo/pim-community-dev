import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleProductSelection } from "./RuleProductSelection";
import {RuleDefinition} from "../../../models/RuleDefinition";
import {Action} from "../../../models/Action";
import {Locale} from "../../../models/Locale";
import {IndexedScopes} from "../../../fetch/ScopeFetcher";
import {ActionLineProps} from "../ActionLineProps";

type Props = {
  register: any;
  translate: Translate;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
};

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({ action, translate, lineNumber, register }) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
      lineNumber={lineNumber}
      register={register}
    />
  );
};

const RulesBuilder: React.FC<Props> = ({
    register,
    translate,
    ruleDefinition,
    locales,
    scopes,
    currentCatalogLocale
  }) => {
  return (
    <>
      <RuleProductSelection
        register={register}
        ruleDefinition={ruleDefinition}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
      />
      {ruleDefinition.actions.map((action, i) => {
        return (
          <ActionLine
            action={action}
            translate={translate}
            key={`action_${i}`}
            lineNumber={i}
            register={register}
          />
        );
      })}
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
