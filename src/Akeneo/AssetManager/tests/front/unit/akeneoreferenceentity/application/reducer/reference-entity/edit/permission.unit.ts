import reducer from 'akeneoreferenceentity/application/reducer/reference-entity/edit/permission';

const initialState = {
  data: [],
  errors: [],
  state: {
    isDirty: false,
    originalData: '',
  },
};

describe('akeneo > reference entity > application > reducer > reference-entity --- permission', () => {
  test('I ignore other commands', () => {
    const newState = reducer(initialState, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(initialState);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual(initialState);
  });

  test('I receive new permissions', () => {
    const newState = reducer(initialState, {
      type: 'PERMISSION_EDITION_RECEIVED',
      permissions: [
        {
          user_group_identifier: 12,
          user_group_name: 'Manager',
          right_level: 'view',
        },
      ],
    });

    expect(newState).toEqual({
      data: [
        {
          user_group_identifier: 12,
          user_group_name: 'Manager',
          right_level: 'view',
        },
      ],
      errors: [],
      state: {
        isDirty: false,
        originalData: '[{"user_group_identifier":12,"user_group_name":"Manager","right_level":"view"}]',
      },
    });
  });

  test('I edit the permissions', () => {
    const newState = reducer(
      {
        data: [
          {
            user_group_identifier: 12,
            user_group_name: 'Manager',
            right_level: 'view',
          },
        ],
        errors: [],
        state: {
          isDirty: false,
          originalData: '[{"user_group_identifier":12,"user_group_name":"Manager","right_level":"view"}]',
        },
      },
      {
        type: 'PERMISSION_EDITION_PERMISSION_UPDATED',
        permissions: [
          {
            user_group_identifier: 12,
            user_group_name: 'Manager',
            right_level: 'edit',
          },
        ],
      }
    );

    expect(newState).toEqual({
      data: [
        {
          user_group_identifier: 12,
          user_group_name: 'Manager',
          right_level: 'edit',
        },
      ],
      errors: [],
      state: {
        isDirty: true,
        originalData: '[{"user_group_identifier":12,"user_group_name":"Manager","right_level":"view"}]',
      },
    });
  });

  test('I save the updated permission', () => {
    const newState = reducer(
      {
        data: [
          {
            user_group_identifier: 12,
            user_group_name: 'Manager',
            right_level: 'view',
          },
        ],
        errors: [
          {
            an: 'error',
          },
        ],
        state: {
          isDirty: false,
          originalData: '[{"user_group_identifier":12,"user_group_name":"Manager","right_level":"view"}]',
        },
      },
      {
        type: 'PERMISSION_EDITION_SUBMISSION',
      }
    );

    expect(newState).toEqual({
      data: [
        {
          user_group_identifier: 12,
          user_group_name: 'Manager',
          right_level: 'view',
        },
      ],
      errors: [],
      state: {
        isDirty: false,
        originalData: '[{"user_group_identifier":12,"user_group_name":"Manager","right_level":"view"}]',
      },
    });
  });

  test('An error can occur', () => {
    const newState = reducer(initialState, {
      type: 'PERMISSION_EDITION_ERROR_OCCURED',
      errors: [
        {
          an: 'error',
        },
      ],
    });

    expect(newState).toEqual({
      data: [],
      errors: [
        {
          an: 'error',
        },
      ],
      state: {
        isDirty: false,
        originalData: '',
      },
    });
  });
});
