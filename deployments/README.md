# Triggers

## List

| Trigger name                     | Parameters                                   | Description                        |
|----------------------------------|----------------------------------------------|------------------------------------|
| delete_instance                  | **instance** (string) : Name of the instance | Delete instance given as parameter |
| clone_and_upgrade_flex_customers |                                              |                                    |
| jenkins_ge2ee_mig                |                                              |                                    |

## How to trigger jobs

From [circleci](https://app.circleci.com/pipelines/github/akeneo/pim-enterprise-dev?branch=master), click on
"Trigger pipeline" button (see screenshot below)

![Trigger pipeline button](./assets/trigger_pipeline_button.png)

Then set following parameter :
* (string) trigger: \<trigger name\>

And required parameters for selected trigger.
E.g. for delete_instance which requires an instance name
* (string) instance: \<your full instance name\>


![Trigger pipeline parameters](./assets/trigger_pipeline_parameters.png)
