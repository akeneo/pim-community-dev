import React, {FunctionComponent} from "react";
import Overview from "./Overview/Overview";
import Widgets from "./Widgets/Widgets";
import {AxesContextProvider} from "../../context/AxesContext";
import {KeyIndicators} from "./KeyIndicators/KeyIndicators";
import {pimTheme} from "akeneo-design-system";
import {ThemeProvider} from "styled-components";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
import {ProductsWithAnImage} from "./KeyIndicators/ProductsWithAnImage";
import {ProductsWithGoodEnrichment} from "./KeyIndicators/ProductsWithGoodEnrichment";

interface DataQualityInsightsDashboardProps {
  timePeriod: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
  axes: string[];
}

const Dashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({timePeriod, catalogLocale, catalogChannel, familyCode, categoryCode, axes}) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AxesContextProvider axes={axes}>
          <div id="data-quality-insights-activity-dashboard">
            <div className="AknSubsection">
              <Overview catalogLocale={catalogLocale} catalogChannel={catalogChannel} timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
              <KeyIndicators channel={catalogChannel} locale={catalogLocale} family={familyCode} category={categoryCode}>
                <ProductsWithAnImage type="has_image"/>
                <ProductsWithGoodEnrichment type="good_enrichment"/>
              </KeyIndicators>
              <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
            </div>
          </div>
        </AxesContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  )
};

export default Dashboard;
