import React, {useEffect, useState} from 'react';

const mediator = require('oro/mediator');

interface OptionsGridProps {
    locales: any;
    options: any;
}

interface EnrichedOption {
    [optionCode: string]: React.Component[];
}

const OptionsGrid = ({locales, options}: OptionsGridProps) => {
    const [selectedOptionCode, setSelectedOptionCode] = useState<undefined|string>(undefined);
    const [enrichedOptions, setEnrichedOptions] = useState<EnrichedOption>({});

    useEffect(() => {
        const handleEnrichRender = (enrichedOptions: EnrichedOption) => {
            setEnrichedOptions(enrichedOptions);
        };

        mediator.on('pim:attribute-options:enrichRender', handleEnrichRender);

        return () => {
            mediator.off('pim:attribute-options:enrichRender', handleEnrichRender);
        }
    }, []);

    useEffect(() => {
        if (options.length !== 0) {
            mediator.trigger('pim:attribute-options:list', options);
        }
    }, [options]);

    let selectedOption: any = undefined;
    if (selectedOptionCode !== undefined) {
        selectedOption = Object.values(options).find((option: any) => option.code === selectedOptionCode);
    }

    return (
        <div>
            <div id="options-list">
                {options !== undefined && options.map((option: any) => {
                    return (
                        <div onClick={() => setSelectedOptionCode(option.code)} key={`option-${option.code}`} className={"option-code"}>
                            {option.code}
                            {enrichedOptions.hasOwnProperty(option.code) && enrichedOptions[option.code].map(component => component)}
                        </div>
                    )
                })}
            </div>
            <div id="options-translations">

                {selectedOption !== undefined && Object.entries(selectedOption.optionValues).map(([localeCode, optionTranslation]: any) => {
                    return (
                        <div key={`option-${selectedOption.code}-${localeCode}`}>
                            <label>{locales.find((locale: any) => locale.code === localeCode).label}</label>
                            <input type={"text"} data-locale={localeCode} defaultValue={optionTranslation.value}/>
                        </div>
                    )
                })}
            </div>
        </div>
    )
}

export default OptionsGrid;
