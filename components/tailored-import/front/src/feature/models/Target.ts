import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';

type TargetAction = 'set' | 'add';
type TargetEmptyAction = 'clear' | 'skip';
type TargetErrorAction = 'skipLine' | 'skipValue';

type AttributeTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  action: TargetAction;
  ifEmpty: TargetEmptyAction;
  onError: TargetErrorAction;
};

type PropertyTarget = {
  code: string;
  type: 'property';
  action: TargetAction;
  ifEmpty: TargetEmptyAction;
  onError: TargetErrorAction;
};

type Target = AttributeTarget | PropertyTarget;

const createAttributeTarget = (code: string, channel: ChannelReference, locale: LocaleReference): AttributeTarget => ({
  code,
  type: 'attribute',
  locale,
  channel,
  action: 'set',
  ifEmpty: 'skip',
  onError: 'skipLine',
});

const createPropertyTarget = (code: string): PropertyTarget => ({
  code,
  type: 'property',
  action: 'set',
  ifEmpty: 'skip',
  onError: 'skipLine',
});

const isAttributeTarget = (target: Target): target is AttributeTarget =>
  'attribute' === target.type && 'locale' in target && 'channel' in target;

const isPropertyTarget = (target: Target): target is PropertyTarget => 'property' === target.type;

export type {AttributeTarget, PropertyTarget, Target};
export {createAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget};
