import React, { useRef, useEffect } from "react";
import styled from "styled-components";
import { useForm, ErrorMessage } from "react-hook-form";
import { InputErrorMsg } from "../../../../components/InputErrorMsg";
import { InputText, FlagLabel } from "../../../../components/InputText";
import { PrimaryButton } from "../../../../components/Buttons/PrimaryButton";
import { Translate } from "../../../../dependenciesTools/provider/applicationDependenciesProvider.type";

const inputCodeErrorMsgId = "inputCodeErrMsg";
const inputCodeName = "code";
const inputLabelErrorMsgId = "inputLabelErrMsg";
const inputLabelName = "label";

const LegendSrOnly = styled.legend`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

type FormDataInput = {
  code: string;
  label?: string;
};

type Props = {
  locale: string
  translate: Translate;
  onSubmit: (formData: FormDataInput, event?: Event) => Promise<any>;
};

type CustomErrors = {
  [key: string]: Array<string>;
};

const initialCustomErrors: CustomErrors = {
  [inputCodeName]: [],
  [inputLabelName]: [],
};

const CreateRulesForm: React.FC<Props> = ({ locale, onSubmit, translate }) => {
  const inputCodeRef = useRef<HTMLInputElement>();
  const [customErrors, setCustomErrors] = React.useState<CustomErrors>(
    initialCustomErrors
  );
  const { errors, formState, handleSubmit, register } = useForm<FormDataInput>({
    mode: "onChange",
  });

  const codeInputRegisterConfig = {
    required: translate(
      "pimee_catalog_rule.form.creation.constraint.code.required"
    ),
    minLength: {
      value: 3,
      message: translate(
        "pimee_catalog_rule.form.creation.constraint.code.too_short",
        { characterLimit: "3" }
      ),
    },
    pattern: {
      value: /^[a-zA-Z0-9_]+$/,
      message: translate(
        "pimee_catalog_rule.form.creation.constraint.code.allowed_characters"
      ),
    },
  };
  const labelInputRegisterConfig = {
    maxLength: 255,
  };

  const manageSubmitError = async (formData: FormDataInput) => {
    const response = await onSubmit(formData);
    if (!response.ok) {
      const data: [any] = await response.json();
      setCustomErrors(
        data.reduce((acc: any, value: any) => {
          return {
            ...acc,
            [value.path]: [...acc[value.path], value.message],
          };
        }, initialCustomErrors)
      );
    }
  };

  useEffect(() => {
    if (inputCodeRef.current) {
      inputCodeRef.current.focus();
    }
  }, [inputCodeRef.current]);

  useEffect(() => {
    if (customErrors[inputCodeName].length && inputCodeRef.current) {
      inputCodeRef.current.focus();
    }
  }, [customErrors]);

  return (
    <form
      className="AknFormContainer"
      data-testid="form-create-rules"
      onSubmit={handleSubmit(manageSubmitError)}
    >
      <fieldset>
        <LegendSrOnly>
          {translate("pimee_catalog_rule.form.creation.title")}
        </LegendSrOnly>
        <div className="AknFieldContainer">
          <InputText
            id="code-input"
            ariaDescribedBy={inputCodeErrorMsgId}
            autoComplete="off"
            label={`${translate("pim_common.code")} ${translate(
              "pim_common.required_label"
            )}`}
            minLength={3}
            name={inputCodeName}
            ref={(currentRef: HTMLInputElement) => {
              inputCodeRef.current = currentRef;
              register(currentRef, codeInputRegisterConfig);
            }}
            required
          />
          <div id={inputCodeErrorMsgId}>
            {customErrors.code.length > 0 &&
              customErrors.code.map((message) => (
                <InputErrorMsg key={`code-${message}`}>{message}</InputErrorMsg>
              ))}
            <ErrorMessage errors={errors} name={inputCodeName}>
              {({ message }) => <InputErrorMsg>{message}</InputErrorMsg>}
            </ErrorMessage>
          </div>
        </div>
        <div className="AknFieldContainer">
          <InputText
            id="label-input"
            ariaDescribedBy={inputLabelErrorMsgId}
            autoComplete="off"
            maxLength={255}
            name={inputLabelName}
            ref={register(labelInputRegisterConfig)}
          >
            <FlagLabel
              locale={locale}
              label={translate("pim_common.label")}
              flagDescription={translate(
                "pimee_catalog_rule.form.creation.english_flag"
              )}
            />
          </InputText>
          <div id={inputLabelErrorMsgId}>
            {customErrors.label.length > 0 &&
              customErrors.label.map((message) => (
                <InputErrorMsg
                  key={`label-${message}`}
                  id={inputLabelErrorMsgId}
                >
                  {message}
                </InputErrorMsg>
              ))}
            <ErrorMessage errors={errors} name={inputLabelName}>
              {({ message }) => (
                <InputErrorMsg id={inputLabelErrorMsgId}>
                  {message}
                </InputErrorMsg>
              )}
            </ErrorMessage>
          </div>
        </div>
      </fieldset>
      <PrimaryButton disabled={!formState.isValid} type="submit">
        {translate("pim_common.save")}
      </PrimaryButton>
    </form>
  );
};

export { CreateRulesForm, FormDataInput };
