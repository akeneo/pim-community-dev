import '@testing-library/jest-dom/extend-expect';
import {fireEvent} from '@testing-library/react';
import {Evaluation} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {renderConsistencyEvaluation, renderEnrichmentEvaluation} from '../../utils/render';

const UserContext = require('pim/user-context');

jest.mock('pim/user-context');

window.dispatchEvent = jest.fn();

beforeEach(() => {
  jest.resetModules();
});

UserContext.get.mockReturnValue('en_US');

describe('Product evaluation tab', () => {
  test('Consistency axis with ongoing criterion evaluation, 2 criteria with recommendations, 1 perfect criterion and 1 not applicable criterion', async () => {
    const {queryAllByTestId, getByText, queryByText, queryAllByText, getAllByTestId} = renderConsistencyEvaluation(
      evaluation1
    );
    assertAxisTitleIsDisplayed(getByText);
    assertAxisGradingInProgressMessageIsDisplayed(getByText);
    assertAxisErrorMessageIsNotDisplayed(queryByText);
    assertAllAxisCriteriaAreDisplayed(queryAllByTestId);
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.axis_error',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.success.criterion',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      /akeneo_data_quality_insights.product_evaluation.messages.not_applicable.message/,
      1,
      queryAllByText
    );

    expect(getAllByTestId('dqiAttributeWithRecommendation').length).toBe(3);
    expect(getAllByTestId('dqiAttributeWithRecommendation')[0].childNodes[0].textContent).toBe('picture');
    expect(getAllByTestId('dqiAttributeWithRecommendation')[1].childNodes[0].textContent).toBe('Product description');
    expect(getAllByTestId('dqiAttributeWithRecommendation')[2].childNodes[0].textContent).toBe('Product description');

    assertAllAttributesLinkClickSendsAnEvent(queryByText, 'consistency', ['description', 'picture']);
  });

  test('Consistency axis with an error, 2 criteria with recommendations, 1 perfect criterion and 1 not applicable criterion', async () => {
    const {queryAllByTestId, getByText, queryByText, queryAllByText, getAllByTestId} = renderConsistencyEvaluation(
      evaluation2
    );
    assertAxisTitleIsDisplayed(getByText);
    assertAxisGradingInProgressMessageIsNotDisplayed(queryByText);
    assertAxisErrorMessageIsDisplayed(getByText);
    assertAllAxisCriteriaAreDisplayed(queryAllByTestId);
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.axis_error',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.success.criterion',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error',
      1,
      queryAllByText
    );
    assertExpectedMessageNumber(
      /akeneo_data_quality_insights.product_evaluation.messages.not_applicable.message/,
      1,
      queryAllByText
    );

    expect(getAllByTestId('dqiAttributeWithRecommendation').length).toBe(1);
    expect(getAllByTestId('dqiAttributeWithRecommendation')[0].innerHTML).toBe('Product description');

    assertAllAttributesLinkClickSendsAnEvent(queryByText, 'consistency', ['description']);
  });

  test('Consistency axis with 4 perfect results and 1 not applicable criterion', async () => {
    const {getByText, queryByText, queryAllByText, queryAllByTestId} = renderConsistencyEvaluation(evaluation3);
    assertAxisTitleIsDisplayed(getByText);
    assertAxisGradingInProgressMessageIsNotDisplayed(queryByText);
    assertAxisErrorMessageIsNotDisplayed(queryByText);
    assertAllAxisCriteriaAreDisplayed(queryAllByTestId);
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.axis_error',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.success.criterion',
      4,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      'akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error',
      0,
      queryAllByText
    );
    assertExpectedMessageNumber(
      /akeneo_data_quality_insights.product_evaluation.messages.not_applicable.message/,
      1,
      queryAllByText
    );

    expect(queryAllByTestId('dqiAttributeWithRecommendation').length).toBe(0);

    const allAttributesLink = queryByText(
      'akeneo_data_quality_insights.product_evaluation.axis.consistency.attributes_link'
    );
    expect(allAttributesLink).toBeFalsy();
  });

  test('Enrichment axis with 2 criteria with recommendations', async () => {
    const {queryByText} = renderEnrichmentEvaluation(evaluation4);
    assertAllAttributesLinkClickSendsAnEvent(queryByText, 'enrichment', ['power_requirements', 'weight']);
  });
});

function assertAxisTitleIsDisplayed(getByText) {
  expect(getByText('akeneo_data_quality_insights.product_evaluation.axis.consistency.title')).toBeTruthy();
}

function assertAxisGradingInProgressMessageIsDisplayed(getByText) {
  expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress')).toBeTruthy();
}

function assertAxisGradingInProgressMessageIsNotDisplayed(queryByText) {
  expect(queryByText('akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress')).toBeFalsy();
}

function assertAxisErrorMessageIsNotDisplayed(queryByText) {
  expect(queryByText('akeneo_data_quality_insights.product_evaluation.messages.error.axis_error')).toBeFalsy();
}

function assertAxisErrorMessageIsDisplayed(getByText) {
  expect(getByText('akeneo_data_quality_insights.product_evaluation.messages.error.axis_error')).toBeTruthy();
}

function assertAllAxisCriteriaAreDisplayed(queryAllByTestId) {
  expect(queryAllByTestId('dqiProductEvaluationCriterion').length).toBe(5);
}

function assertExpectedMessageNumber(criteriaStatus: string | RegExp, expectedNumber: number, queryAllByText) {
  expect(queryAllByText(criteriaStatus).length).toBe(expectedNumber);
}

function assertAllAttributesLinkClickSendsAnEvent(queryByText, axis: string, expectedAttributeCodes: string[]) {
  const events = {
    consistency: DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
    enrichment: DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  };

  const allAttributesLink = queryByText(`akeneo_data_quality_insights.product_evaluation.axis.${axis}.attributes_link`);
  expect(allAttributesLink).toBeTruthy();
  fireEvent.click(allAttributesLink);
  const customEvents = window.dispatchEvent.mock.calls.filter(event => event[0].constructor.name === 'CustomEvent')[0];
  expect(customEvents.length).toBe(1);
  expect(customEvents[0].type).toBe(events[axis]);
  expect(customEvents[0].detail.attributes).toMatchObject(expectedAttributeCodes);
}

const evaluation1: Evaluation = {
  criteria: [
    {
      status: 'in_progress',
      code: 'consistency_spelling',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
    {
      status: 'done',
      code: 'consistency_textarea_lowercase_words',
      improvable_attributes: ['description', 'picture'],
      rate: {
        rank: 'C',
        value: 76,
      },
    },
    {
      status: 'done',
      code: 'consistency_textarea_uppercase_words',
      improvable_attributes: ['description'],
      rate: {
        rank: 'A',
        value: 95,
      },
    },
    {
      status: 'done',
      code: 'consistency_text_title_formatting',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'not_applicable',
      code: 'not_applicable_criterion',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
  ],
  rate: {
    rank: 'B',
    value: 85,
  },
};
const evaluation2: Evaluation = {
  criteria: [
    {
      status: 'in_progress',
      code: 'consistency_spelling',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
    {
      status: 'error',
      code: 'consistency_textarea_lowercase_words',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
    {
      status: 'done',
      code: 'consistency_textarea_uppercase_words',
      improvable_attributes: ['description'],
      rate: {
        rank: 'A',
        value: 95,
      },
    },
    {
      status: 'done',
      code: 'consistency_text_title_formatting',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'not_applicable',
      code: 'not_applicable_criterion',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
  ],
  rate: {
    rank: 'B',
    value: 85,
  },
};

const evaluation3: Evaluation = {
  criteria: [
    {
      status: 'done',
      code: 'consistency_spelling',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'done',
      code: 'consistency_textarea_lowercase_words',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'done',
      code: 'consistency_textarea_uppercase_words',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'done',
      code: 'consistency_text_title_formatting',
      improvable_attributes: [],
      rate: {
        rank: 'A',
        value: 100,
      },
    },
    {
      status: 'not_applicable',
      code: 'not_applicable_criterion',
      improvable_attributes: [],
      rate: {
        rank: null,
        value: null,
      },
    },
  ],
  rate: {
    rank: 'A',
    value: 100,
  },
};

const evaluation4: Evaluation = {
  criteria: [
    {
      status: 'done',
      code: 'completeness_of_non_required_attributes',
      improvable_attributes: ['weight', 'power_requirements'],
      rate: {
        rank: 'E',
        value: 20,
      },
    },
    {
      status: 'done',
      code: 'completeness_of_required_attributes',
      improvable_attributes: ['power_requirements'],
      rate: {
        rank: 'E',
        value: 50,
      },
    },
  ],
  rate: {
    rank: 'E',
    value: 35,
  },
};
