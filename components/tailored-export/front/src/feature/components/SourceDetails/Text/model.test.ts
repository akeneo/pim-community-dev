import {isTextSource, TextSource} from './model';
import {Source} from '../../../models';

describe('it validates a text source', () => {
  const textSourceWithoutOperation: TextSource = {
    uuid: '123',
    code: 'a code',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: {},
    selection: {type: 'code'},
  };

  test('it validates a text source without operation', () => {
    expect(isTextSource(textSourceWithoutOperation)).toBe(true);
  });

  test('it validates a text source with default value operation', () => {
    const textSourceWithDefaultValueOperation: Source = {
      ...textSourceWithoutOperation,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    };

    expect(isTextSource(textSourceWithDefaultValueOperation)).toBe(true);
  });

  test('it validates a text source with clean HTML tags operation', () => {
    const textSourceWithCleanHTMLTagsOperation: TextSource = {
      ...textSourceWithoutOperation,
      operations: {
        clean_html_tags: {
          type: 'clean_html_tags',
          value: true,
        },
      },
    };

    expect(isTextSource(textSourceWithCleanHTMLTagsOperation)).toBe(true);
  });

  test('it invalidates a text source with not valid operation', () => {
    const textSourceWithInvalidOperation: TextSource = {
      ...textSourceWithoutOperation,
      operations: {
        // @ts-expect-error invalid operation
        foo: 'bar',
      },
    };

    expect(isTextSource(textSourceWithInvalidOperation)).toBe(false);
  });
});
