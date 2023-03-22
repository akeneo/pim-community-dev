import React, {FC} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {SourcePlaceholder} from './SourcePlaceholder';
import {Source} from '../models/Source';
import {SourceErrors} from '../models/SourceErrors';
import {SourceUuidPlaceholder} from './SourceUuidPlaceholder';
import {Target} from '../models/Target';
import {RequirementsCollapse} from './RequirementsCollapse';
import {SourceSelection} from './SourceSelection/SourceSelection';
import {SourceParameters} from './SourceParameters/SourceParameters';

type Props = {
    target: Target | null;
    source: Source;
    onChange: (value: Source) => void;
    errors: SourceErrors | null;
};

export const SourcePanel: FC<Props> = ({target, source, onChange, errors}) => {
    if (null === target) {
        return <SourcePlaceholder />;
    }

    if ('uuid' === target.code) {
        return <SourceUuidPlaceholder targetLabel={target.label} />;
    }

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>{target.label}</SectionTitle.Title>
            </SectionTitle>
            <RequirementsCollapse target={target} />
            <SourceSelection source={source} target={target} errors={errors} onChange={onChange} />
            <SourceParameters source={source} target={target} onChange={onChange} errors={errors} />
        </>
    );
};
