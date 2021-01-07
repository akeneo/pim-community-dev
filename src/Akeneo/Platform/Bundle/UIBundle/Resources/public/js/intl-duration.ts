import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type IntlDuration = {
  translationKey: string;
  value: number;
};

const translateDuration = (...args: IntlDuration[]): string => {
  const translate = useTranslate();
  let values = args.filter((duration: IntlDuration) => duration.value > 0);

  if (values.length === 0 && args.length > 0) {
    values = [args[args.length - 1]];
  }

  return values
    .map((duration: IntlDuration) =>
      translate(duration.translationKey, {count: duration.value.toString()}, duration.value)
    )
    .join(' ');
};

export const formatSecondsIntl = (duration: number): string => {
  const days = Math.floor(duration / 86400);
  duration = duration - days * 86400;
  const hours = Math.floor(duration / 3600);
  duration = duration - hours * 3600;
  const minutes = Math.floor(duration / 60);
  const seconds = duration - minutes * 60;

  if (days > 0) {
    return translateDuration(
      {translationKey: 'duration.days', value: days},
      {translationKey: 'duration.hours', value: hours}
    );
  }
  if (hours > 0) {
    return translateDuration(
      {translationKey: 'duration.hours', value: hours},
      {translationKey: 'duration.minutes', value: minutes}
    );
  }
  if (minutes > 0) {
    return translateDuration(
      {translationKey: 'duration.minutes', value: minutes},
      {translationKey: 'duration.seconds', value: seconds}
    );
  }
  return translateDuration({translationKey: 'duration.seconds', value: seconds});
};
