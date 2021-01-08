import {formatSecondsIntl} from 'pimui/js/intl-duration';

const translate = (key: string, _params: any, count: number) => {
  switch (key) {
    case 'duration.days':
      return `${count} day(s)`;
    case 'duration.hours':
      return `${count} hour(s)`;
    case 'duration.minutes':
      return `${count} minute(s)`;
    case 'duration.seconds':
      return `${count} second(s)`;
    default:
      return key;
  }
};

const createDuration = (days: number, hours: number, minutes: number, seconds: number): number => {
  return seconds + minutes * 60 + hours * 3600 + days * 86400;
};

describe('>>>TOOLS --- intl-duration', () => {
  test('it display 0 seconds when empty', () => {
    expect(formatSecondsIntl(translate, createDuration(0, 0, 0, 0))).toBe('0 second(s)');
  });

  test('it display only one unit when perfectly rounded', () => {
    expect(formatSecondsIntl(translate, createDuration(0, 0, 0, 1))).toBe('1 second(s)');
    expect(formatSecondsIntl(translate, createDuration(0, 0, 1, 0))).toBe('1 minute(s)');
    expect(formatSecondsIntl(translate, createDuration(0, 1, 0, 0))).toBe('1 hour(s)');
    expect(formatSecondsIntl(translate, createDuration(1, 0, 0, 0))).toBe('1 day(s)');
  });

  test('it display only 2 not-empty successive units', () => {
    expect(formatSecondsIntl(translate, createDuration(0, 0, 1, 1))).toBe('1 minute(s) 1 second(s)');
    expect(formatSecondsIntl(translate, createDuration(0, 1, 1, 0))).toBe('1 hour(s) 1 minute(s)');
    expect(formatSecondsIntl(translate, createDuration(1, 1, 0, 0))).toBe('1 day(s) 1 hour(s)');
  });

  test('it display only the largest unit when not successives', () => {
    expect(formatSecondsIntl(translate, createDuration(1, 0, 0, 1))).toBe('1 day(s)');
    expect(formatSecondsIntl(translate, createDuration(1, 0, 0, 1))).toBe('1 day(s)');
  });
});
