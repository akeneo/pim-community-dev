import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleProductSelection } from "./RuleProductSelection";
import {RuleDefinition} from "../../../models/RuleDefinition";
import {Action} from "../../../models/Action";
import {Locale} from "../../../models/Locale";

type Props = {
  register: any;
  translate: Translate;
  ruleDefinition: RuleDefinition;
  activatedLocales: Locale[];
};

const ActionLine: React.FC<{ action: Action, translate: Translate }> = ({ action, translate }) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
    />
  );
};

const RulesBuilder: React.FC<Props> = ({ register, translate, ruleDefinition, activatedLocales }) => {
  return (
    <>
      <RuleProductSelection register={register} ruleDefinition={ruleDefinition} translate={translate} activatedLocales={activatedLocales}/>
      {ruleDefinition.actions.map((action, i) => {
        return <ActionLine
          action={action}
          translate={translate}
          key={`action_${i}`}/>
      })}
    </>
  );
};

RulesBuilder.displayName = "RulesBuilder";

export { RulesBuilder };
