#!groovy

import org.csanchez.jenkins.plugins.kubernetes.pipeline.PodTemplateAction
import org.apache.commons.lang.ArrayUtils

def builded_image = [:]
docker_registry = "eu.gcr.io/akeneo-ci/"

stage("PreBuild") {

    milestone 1
    input 'Launch your build?'
    milestone 2

    tasks = [:]

    builded_image["httpd"] =  "httpd:2.4"
    builded_image["php"] =  "php:7.1-fpm"
    builded_image["elasticsearch"] =  "elasticsearch:5.5"
    builded_image["selenium-standalone-firefox"] =  "selenium:standalone-firefox-2.53.1-beryllium"

    for (element in builded_image) {
    taskName =element.key
    image_name_base = element.value
      tasks["${taskName}"] = {
          withDockerScm({
              image_name = "${docker_registry}${image_name_base}-${env_BRANCH_NAME}"
              sh "gcloud -- pull ${image_name_base}"
              sh "docker build -t ${image_name} .ci/Dockerfiles/${taskName}/"
              sh "gcloud docker -- push ${image_name}"
          })
      }

    }

    parallel tasks
}

stage("Build") {
    parallel(
        "pim-ce": {withBuildNode({
            checkout scm
            container("php") {
                sh "composer update --ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs --no-suggest"
                sh "bin/console --ansi assets:install"
                sh "bin/console --ansi pim:installer:dump-require-paths"
            }
            container("node") {
                sh "yarn config set cache-folder /shared/.yarn"
                sh "yarn install --no-progress"
                sh "yarn run webpack"
            }
            container("docker") {
                sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                sh "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"
                sh "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"
                sh "sed \"\$a    installer_data: 'PimInstallerBundle:minimal'\n\" app/config/parameters_test.yml"
                sh "cp behat.ci.yml behat.yml"

                sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce ."
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce"
            }
        })},
        "pim-ee": {withBuildNode({
            checkout([$class: 'GitSCM',
              branches: [[name: 'master']],
              userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
            ])
            // Required to avoid permission error when "composer update"
            sh "mkdir -m 777 vendor"

            container("php") {
                sh "composer update --ansi --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts --ignore-platform-reqs --no-suggest"

                // Required to avoid permission error when "deleteDir()"
                sh "chmod 777 -R vendor/akeneo"

                dir('vendor/akeneo/pim-community-dev') {
                    deleteDir()
                    checkout scm
                }

                sh "php -d memory_limit=-1 /usr/bin/composer --ansi -n run-script post-update-cmd"
                sh "bin/console --ansi assets:install"
                sh "bin/console --ansi pim:installer:dump-require-paths"
            }
            container("node") {
                sh "yarn config set cache-folder /shared/.yarn"
                sh "yarn install --no-progress"
                sh "yarn run webpack"
            }
            container("docker") {
                // Compatibility layer while the EE is not up to date with the new CI
                sh "cp vendor/akeneo/pim-community-dev/Dockerfile ."
                sh "cp -R vendor/akeneo/pim-community-dev/.ci ."
                sh "sed -i \"s#http://akeneo#http://127.0.0.1#g\" behat.ci.yml"
                sh "sed -i \"s#http://selenium#http://127.0.0.1#g\" behat.ci.yml"

                sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                sh "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"
                sh "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"
                sh "sed \"\$a    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" app/config/parameters_test.yml"
                sh "cp behat.ci.yml behat.yml"

                sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee ."
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee"
            }
        })}
    )
}

stage("Test") {
    try {
        parallel(
            "phpunit": {
                withPhp({
                    try {
                        sh "cd /home/jenkins/pim && vendor/bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit ${env.WORKSPACE}/junit_output.xml"
                    } finally {
                        junit "junit_output.xml"
                    }
                })
            },
            "phpspec": {
                withPhp({
                    sh "cd /home/jenkins/pim && chown -R phpuser ."

                    try {
                        sh "cd /home/jenkins/pim && su phpuser -c 'vendor/bin/phpspec run --format=junit > junit_output.xml'"
                        sh "mv /home/jenkins/pim/junit_output.xml ${env.WORKSPACE}/"
                    } finally {
                        junit "junit_output.xml"
                    }
                })
            },
            "php-cs-fixer": {
                withPhp({
                    try {
                        sh "cd /home/jenkins/pim && vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php --format=junit > ${env.WORKSPACE}/junit_output.xml"
                    } finally {
                        junit "junit_output.xml"
                    }
                })
            },
            "grunt": {
                withNode({
                    sh "cd /home/jenkins/pim && yarn run webpack --no-progress"
                    sh "cd /home/jenkins/pim && yarn run lint"
                })
            },
            "php-coupling-detector": {
                withPhp({
                    sh "cd /home/jenkins/pim && vendor/bin/php-coupling-detector detect --config-file=.php_cd.php src"
                })
            },
            "phpunit-integration-ce": {
                queue({
                    files = sh (returnStdout: true, script: 'find /home/jenkins/pim/src -name "*Integration.php"').tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (file in files) {
                        messages.add([
                            [container: "php", script: "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "bin/console --env=test pim:install --force"],
                            [
                                container: "php",
                                junit: [in: "/home/jenkins/pim/", name: "junit_output.xml"],
                                script: "php -d error_reporting='E_ALL' vendor/bin/phpunit -c app/phpunit.xml.dist " + file + " --log-junit junit_output.xml"
                            ]
                        ])
                    }

                    return messages
                }, 40, "ce")
            },
            "behat-ce": {
                queue({
                    def tags = "~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
                    scenarios = sh (returnStdout: true, script: "cd /home/jenkins/pim && php vendor/bin/behat --list-scenarios -c behat.ci.yml --tags=\"${tags}\"").tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (scenario in scenarios) {
                        messages.add([
                            [container: "php", script: "bin/console --env=behat --quiet pim:install --force"],
                            [container: "php", script: "chmod 777 -R var/cache/behat"],
                            [container: "php", script: "touch var/logs/behat.log"],
                            [container: "php", script: "chmod 777 -R var/logs/behat.log"],
                            [container: "php", script: "mkdir -m 777 -p app/build/logs/behat app/build/screenshots"],
                            [
                                container: "php",
                                junit: [in: "/home/jenkins/pim/app/build/logs/behat/", name: "*.xml"],
                                artifacts: [in: "/home/jenkins/pim/app/build", name: "*.png"],
                                script: "php vendor/bin/behat -c behat.ci.yml --tags=\"${tag}\" --strict -vv " + scenario
                            ]
                        ])
                    }

                    return messages
                }, 150, "ce")
            },
            "phpunit-integration-ee": {
                queue({
                    files = sh (returnStdout: true, script: 'find /home/jenkins/pim/src -name "*Integration.php"').tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (file in files) {
                        messages.add([
                            [container: "php", script: "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "bin/console --env=test pim:install --force"],
                            [
                                container: "php",
                                junit: [in: "/home/jenkins/pim/", name: "junit_output.xml"],
                                script: "php -d error_reporting='E_ALL' vendor/bin/phpunit -c app/phpunit.xml.dist " + file + " --log-junit junit_output.xml"
                            ]
                        ])
                    }

                    return messages
                }, 20, "ee")
            },
            "behat-ee": {
                queue({
                    def tags = "~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
                    scenarios = sh (returnStdout: true, script: "cd /home/jenkins/pim && php vendor/bin/behat --list-scenarios -c behat.ci.yml --tags=\"${tags}\"").tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (scenario in scenarios) {
                        messages.add([
                            [container: "php", script: "bin/console --env=behat --quiet pim:install --force"],
                            [container: "php", script: "chmod 777 -R var/cache/behat"],
                            [container: "php", script: "touch var/logs/behat.log"],
                            [container: "php", script: "chmod 777 -R var/logs/behat.log"],
                            [container: "php", script: "mkdir -m 777 -p app/build/logs/behat app/build/screenshots"],
                            [
                                container: "php",
                                junit: [in: "/home/jenkins/pim/app/build/logs/behat/", name: "*.xml"],
                                artifacts: [in: "/home/jenkins/pim/app/build", name: "*.png"],
                                script: "php vendor/bin/behat -c behat.ci.yml --tags=\"${tag}\" --strict -vv " + scenario
                            ]
                        ])
                    }

                    return messages
                }, 200, "ee")
            }
        )
    } finally {
        clearTemplateNames()
        podTemplate(label: "cleanup", containers: [
            containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")])
        ], volumes: [
            hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
        ]) {
            node("cleanup") {
                container("docker") {
                    sh "gcloud -q container images delete eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce"
                    sh "gcloud -q container images delete eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee"
                    builded_image.each{ image_name ->
                        sh "gcloud -q container images delete ${image_name}-${env.BRANCH_NAME}"
                    }
                }
            }
        }
    }
}

def withBuildNode(body) {
    clearTemplateNames()
    podTemplate(label: "build", containers: [
        containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")]),
        containerTemplate(name: "php", ttyEnabled: true, alwaysPullImage: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1-fpm", envVars: [containerEnvVar(key: "COMPOSER_HOME", value: "/shared/.composer")]),
        containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8")
    ], volumes: [
        nfsVolume(mountPath: '/shared', serverAddress: "${env.NFS_IP}", serverPath: '/exports', readOnly: false),
        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
    ]) {
        node("build") {
            dir('/home/jenkins/pim') {
                body()
            }
        }
    }
}

def withPhp(body) {
    clearTemplateNames()
    podTemplate(label: "php", containers: [
        containerTemplate(name: "php", ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1-fpm")
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ]) {
        node("php") {
            container("php") {
                body()
            }
        }
    }
}

def withNode(body) {
    clearTemplateNames()
    podTemplate(label: "node", containers: [
        containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8")
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ]) {
        node("node") {
            container("node") {
                body()
            }
        }
    }
}

def withDockerScm(body) {
    clearTemplateNames()
    podTemplate(label: "dockerscm", containers: [
        containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")])
    ], volumes: [
        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
    ]) {
        node("dockerscm") {
            checkout scm

            container("docker") {
                body()
            }
        }
    }
}

def queue(body, scale, edition) {
    clearTemplateNames()
    def uuid = UUID.randomUUID().toString()
    podTemplate(label: "pubsub-" + uuid, containers: [
        containerTemplate(name: "php", ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1-fpm"),
        containerTemplate(name: "gcloud", alwaysPullImage: true, ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/gcloud", envVars: [containerEnvVar(key: "PUBSUB_PROJECT_ID", value: "akeneo-ci")])
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-${edition}\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ], volumes: [
        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock"),
        hostPathVolume(hostPath: "/usr/bin/docker", mountPath: "/usr/bin/docker")
    ]) {
        node("pubsub-" + uuid) {
            container("php") {
                def messages = body()
            }

            container("gcloud") {
                sh "gcloud.phar pubsub:topic:create ${NODE_NAME}"
                sh "gcloud.phar pubsub:topic:create ${NODE_NAME}-results"
                sh "gcloud.phar pubsub:subscription:create ${NODE_NAME} ${NODE_NAME}-subscription"
                sh "gcloud.phar pubsub:subscription:create ${NODE_NAME}-results ${NODE_NAME}-results-subscription"

                def size = messages.size()

                writeJSON file: 'output.json', json: messages
                sh "gcloud.phar pubsub:message:publish ${NODE_NAME} output.json"

                sh "sed -i 's#JOB_NAME#${NODE_NAME}#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"
                sh "sed -i 's#SUBSCRIPTION_NAME#${NODE_NAME}-subscription#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"
                sh "sed -i 's#RESULT_TOPIC#${NODE_NAME}-results#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"
                sh "sed -i 's#PIM_IMAGE#eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-${edition}#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"

                try {
                    sh "kubectl apply -f /home/jenkins/pim/.ci/k8s/"
                    sh "kubectl scale --replicas=${scale} jobs/${NODE_NAME}"
                    sh "gcloud.phar job:wait ${NODE_NAME}-results-subscription ${size} ${env.WORKSPACE} --ansi"
                } finally {
                    sh "kubectl delete job ${NODE_NAME} || true"
                    sh "gcloud.phar pubsub:topic:delete ${NODE_NAME} || true"
                    sh "gcloud.phar pubsub:topic:delete ${NODE_NAME}-results || true"
                    sh "gcloud.phar pubsub:subscription:delete ${NODE_NAME}-subscription || true"
                    sh "gcloud.phar pubsub:subscription:delete ${NODE_NAME}-results-subscription || true"

                    junit allowEmptyResults: true, testResults: 'junit/**/*.xml'
                    archiveArtifacts allowEmptyArchive: true, artifacts: 'artifacts/**/*.png'
                }
            }
        }
    }
}

@NonCPS
def clearTemplateNames() {
    // see https://issues.jenkins-ci.org/browse/JENKINS-42184
    def action = currentBuild.rawBuild.getAction(PodTemplateAction.class);
    if(action) { action.names.clear() }
}

@NonCPS
def hasChanged(lookup) {
    for (cs in currentBuild.rawBuild.getChangeSets()) {
        for (item in cs.getItems()) {
            for (path in item.getAffectedPaths()) {
                if (path.equals(lookup)) {
                    return true
                }

                if (path.lastIndexOf("/") > 0) {
                    if (path.substring(0, path.lastIndexOf("/")).equals(lookup)) {
                        return true
                    }
                }
            }
        }
    }

    return false
}
