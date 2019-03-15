/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseView = require("pimui/js/view/base");
import * as _ from "underscore";

const __ = require("oro/translator");
const FetcherRegistry = require("pim/fetcher-registry");
const Routing = require("routing");
const UserContext = require("pim/user-context");

const pendingTemplate = require("teamwork-assistant/templates/widget/project-completeness-data-pending");
const template = require("teamwork-assistant/templates/widget/project-completeness-data");

interface Config {
  labels: {
    ratioTodo: string;
    ratioInProgress: string;
    ratioDone: string;
    todo: string;
    inProgress: string;
    done: string;
    displayProducts: string;
  };
}

interface Completeness {
  is_completeness_computed: boolean;
  is_complete: boolean;
  products_count_done: number;
  products_count_in_progress: number;
  products_count_todo: number;
  ratio_done: number;
  ratio_in_progress: number;
  ratio_todo: number;
}

/**
 * Project completeness.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectCompletenessData extends BaseView {
  private readonly pendingTemplate: any = _.template(pendingTemplate);
  private readonly template: any = _.template(template);

  private readonly config: Config;

  /**
   * Used to clean the request polling timeout in case of re-render
   * (selection of another project or contributor).
   */
  private pollCompletenessTimeoutID: any;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super({ ...options, className: "AknProjectWidget-boxes" });

    this.config = options.config;
  }

  /**
   * {@inheritdoc}
   *
   * Render completeness data of the contributor for the given project.
   * If username is null, it renders global completeness of the project.
   */
  public render(): BaseView {
    this.$el.html(this.pendingTemplate());

    const data = this.getFormData();
    let contributorUsername = null;

    if (this.getFormModel().has("currentContributorUsername")) {
      contributorUsername = data.currentContributorUsername;
    }

    const displayLinks = _.contains(
      [UserContext.get("username"), null],
      contributorUsername
    );

    const noContributor = null === contributorUsername;
    const isOwner =
      data.currentProject.owner.username === UserContext.get("username");

    const urls: { [status: string]: string } = {};
    _.each(["todo", "inprogress", "done"], status => {
      urls[status] = Routing.generate("teamwork_assistant_project_show", {
        identifier: data.currentProjectCode,
        status:
          noContributor && isOwner ? "owner-" + status : "contributor-" + status
      });
    });

    this.pollCompleteness(data.currentProjectCode, contributorUsername).then(
      completeness =>
        this.$el.html(
          this.template({
            completeness,
            todoLabel: __(this.config.labels.todo),
            inProgressLabel: __(this.config.labels.inProgress),
            displayProductsLabel: __(this.config.labels.displayProducts),
            ratioTodoLabel: __(this.config.labels.ratioTodo),
            ratioInProgressLabel: __(this.config.labels.ratioInProgress),
            ratioDoneLabel: __(this.config.labels.ratioDone),
            doneLabel: __(this.config.labels.done),
            displayLinks: displayLinks,
            urls: urls
          })
        )
    );

    return this;
  }

  /** 
   * Poll the project completeness every POLLING_RATE seconds, until the property
   * 'is_completeness_computed' return 'true' then resolve the Completeness.
   */
  private pollCompleteness(
    currentProjectCode: string,
    contributorUsername: string
  ): Promise<Completeness> {
    const POLLING_RATE = 5000;

    clearTimeout(this.pollCompletenessTimeoutID);

    const fetchCompleteness = (resolve: (completeness: Completeness) => void) =>
      FetcherRegistry.getFetcher("project")
        .getCompleteness(currentProjectCode, contributorUsername)
        .then((completeness: Completeness) => {
          if (true === completeness.is_completeness_computed) {
            return resolve(completeness);
          }

          this.pollCompletenessTimeoutID = setTimeout(
            () => fetchCompleteness(resolve),
            POLLING_RATE
          );
        });

    return new Promise(fetchCompleteness);
  }
}

export = ProjectCompletenessData;
