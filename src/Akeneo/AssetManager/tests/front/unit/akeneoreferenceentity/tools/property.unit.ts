import {accessProperty} from 'akeneoassetmanager/tools/property';

describe('akeneo > asset family > tools --- property', () => {
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
