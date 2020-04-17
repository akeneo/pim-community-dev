import React from "react";
import { Translate } from "../../../dependenciesTools";
import { RuleDefinition } from "../../../models/RuleDefinition";
import { FlagLabel, InputText } from "../../../components/InputText";
import { InputNumber } from "../../../components/InputNumber";
import { SmallHelper } from "../../../components/SmallHelper";
import { Locale } from "../../../models/Locale";

type Props = {
  locales?: Locale[];
  register: any;
  ruleDefinition: RuleDefinition;
  setRuleDefinition: React.Dispatch<React.SetStateAction<RuleDefinition>>;
  translate: Translate;
};

const RuleProperties: React.FC<Props> = ({
  register,
  locales,
  ruleDefinition,
  setRuleDefinition,
  translate
}) => {
  return (
    <div className="AknFormContainer">
      <SmallHelper>Page under construction</SmallHelper>
      <div className="AknFormContainer">
        <div className="AknFieldContainer">
          <InputText
            disabled
            id="edit-rules-input-code"
            name="code"
            label={translate("pim_common.code")}
            readOnly
            ref={register}
          />
        </div>
        <div className="AknFieldContainer">
          <InputNumber
            name="priority"
            id="edit-rules-input-priority"
            label={translate("pimee_catalog_rule.form.edit.priority.label")}
            onChange={event => {
              setRuleDefinition({
                ...ruleDefinition,
                priority: Number(event.target.value)
              });
            }}
            ref={register}
          />
        </div>
        {locales &&
          locales.map(locale => {
            return (
              <div className="AknFieldContainer" key={locale.code}>
                <InputText
                  name={`labels.${locale.code}`}
                  id={`edit-rules-input-label-${locale.code}`}
                  label={locale.label}
                  onChange={event => {
                    setRuleDefinition({
                      ...ruleDefinition,
                      labels: {
                        ...ruleDefinition.labels,
                        [locale.code]: event.target.value
                      }
                    });
                  }}
                  ref={register}
                >
                  <FlagLabel
                    htmlFor={`edit-rules-input-label-${locale.code}`}
                    locale={locale.code}
                    label={locale.label}
                    flagDescription={locale.label}
                  />
                </InputText>
              </div>
            );
          })}
      </div>
    </div>
  );
};

RuleProperties.displayName = "RuleProperties";

export { RuleProperties };
