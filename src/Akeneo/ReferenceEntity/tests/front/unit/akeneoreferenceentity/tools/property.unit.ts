import {accessProperty} from 'akeneoreferenceentity/tools/property';

describe('akeneo > reference entity > tools --- property', () => {
  test('I can access property', () => {
    expect(
      accessProperty(
        {
          nice: {
            awesome: [
              12,
              {
                small: {
                  object: 'cool',
                },
              },
            ],
          },
        },
        'nice.awesome.1.small.object'
      )
    ).toEqual('cool');
  });

  expect(
    accessProperty(
      {
        nice: {
          awesome: [
            12,
            {
              small: {
                object: 'cool',
              },
            },
          ],
        },
      },
      'nice.awesome.0.small.object',
      'damned'
    )
  ).toEqual('damned');
});
