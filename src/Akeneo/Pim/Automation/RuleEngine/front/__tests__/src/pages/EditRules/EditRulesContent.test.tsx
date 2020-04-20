import React from "react";
import { EditRulesContent } from "../../../../src/pages/EditRules/EditRulesContent";
import userEvent from "@testing-library/user-event";
import { render, act } from "../../../../test-utils";

jest.mock("../../../../src/dependenciesTools/provider/dependencies.ts");

describe("EditRulesContent", () => {
  it("should display an unsaved changes alert after user have changed an input", async () => {
    // Given
    const ruleDefinitionCode = "my_code";
    const ruleDefinition = {
      id: 1,
      code: ruleDefinitionCode,
      type: "product",
      priority: 0,
      actions: [],
      conditions: [],
      labels: { en_US: "toto" },
    };
    const locales = [
      {
        code: "en_US",
        label: "English (United States)",
        region: "United States",
        language: "English",
      },
    ];
    // When
    const { findByText, findByLabelText } = render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
      />,
      {
        legacy: true,
      }
    );
    const propertiesTab = (await findByText(
      "pim_common.properties"
    )) as HTMLButtonElement;
    act(() => userEvent.click(propertiesTab));
    const inputPriority = (await findByLabelText(
      "pimee_catalog_rule.form.edit.priority.label"
    )) as HTMLInputElement;
    await act(() => userEvent.type(inputPriority, "1"));
    // Then
    expect(await findByText("There are unsaved changes.")).toBeInTheDocument();
  });
});
