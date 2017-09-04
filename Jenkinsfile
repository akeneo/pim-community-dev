#!groovy

import org.csanchez.jenkins.plugins.kubernetes.pipeline.PodTemplateAction
import org.apache.commons.lang.ArrayUtils

stage("PreBuild") {
    tasks = [:]

    if (hasChanged(".ci/Dockerfiles/httpd/2.4")) {
        tasks['httpd-2.4'] = {
            withDockerScm({
                sh "docker build -t eu.gcr.io/akeneo-ci/httpd:2.4 .ci/Dockerfiles/httpd/2.4"
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/httpd:2.4"
            })
        }
    }

    if (hasChanged(".ci/Dockerfiles/php/7.1-fpm")) {
        tasks['php-7.1-fpm'] = {
            withDockerScm({
                sh "docker build -t eu.gcr.io/akeneo-ci/php:7.1-fpm .ci/Dockerfiles/php/7.1-fpm"
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/php:7.1-fpm"
            })
        }
    }

    if (hasChanged(".ci/Dockerfiles/elasticsearch/5.5")) {
        tasks['elasticsearch-5.5'] = {
            withDockerScm({
                sh "docker build -t eu.gcr.io/akeneo-ci/elasticsearch:5.5 .ci/Dockerfiles/elasticsearch/5.5"
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/elasticsearch:5.5"
            })
        }
    }

    if (hasChanged(".ci/Dockerfiles/selenium/standalone-firefox-2.53.1-beryllium")) {
        tasks['selenium-standalone-firefox-2.53.1-beryllium'] = {
            withDockerScm({
                sh "docker build -t eu.gcr.io/akeneo-ci/selenium:standalone-firefox-2.53.1-beryllium .ci/Dockerfiles/selenium/standalone-firefox-2.53.1-beryllium"
                sh "gcloud docker -- push eu.gcr.io/akeneo-ci/selenium:standalone-firefox-2.53.1-beryllium"
            })
        }
    }

    parallel tasks
}

stage("Build") {

    tasks = [:]

    tasks['pim-ce'] = {
        clearTemplateNames()
        podTemplate(label: "pimce", containers: [
            containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")]),
            containerTemplate(name: "composer", ttyEnabled: true, alwaysPullImage: true, command: 'cat', image: "composer", envVars: [containerEnvVar(key: "COMPOSER_HOME", value: "/shared/.composer")]),
            containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8")
        ], volumes: [
            nfsVolume(mountPath: '/shared', serverAddress: '10.3.248.208', serverPath: '/exports', readOnly: false),
            hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
        ]) {
            node("pimce") {
                dir('/home/jenkins/pim') {
                    checkout scm
                    container("composer") {
                        sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs --no-suggest"
                        sh "bin/console assets:install"
                        sh "bin/console pim:installer:dump-require-paths"
                    }
                    container("node") {
                        sh "npm config set cache /shared/.npm --global"
                        sh "npm install"
                        sh "npm run webpack"
                    }
                    container("docker") {
                        sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce ."
                        sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce"
                    }
                }
            }
        }
    }

    tasks['pim-ee'] = {
        clearTemplateNames()
        podTemplate(label: "pimee", containers: [
            containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")]),
            containerTemplate(name: "php", ttyEnabled: true, alwaysPullImage: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1-fpm", envVars: [containerEnvVar(key: "COMPOSER_HOME", value: "/shared/.composer")]),
            containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8")
        ], volumes: [
            nfsVolume(mountPath: '/shared', serverAddress: '10.3.248.208', serverPath: '/exports', readOnly: false),
            hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
        ]) {
            node("pimee") {
                dir('/home/jenkins/pim') {
                    checkout([$class: 'GitSCM',
                      branches: [[name: 'master']],
                      userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
                    ])
                    // Required to avoid permission error when "composer update"
                    sh "mkdir -m 777 vendor"

                    container("php") {
                        sh "php -d memory_limit=-1 /usr/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts --ignore-platform-reqs --no-suggest"
                        // Required to avoid permission error when "deleteDir()"
                        sh "chmod 777 -R vendor/akeneo"
                        dir('vendor/akeneo/pim-community-dev') {
                            deleteDir()
                            checkout scm
                        }
                        sh "php -d memory_limit=-1 /usr/bin/composer -n run-script post-update-cmd"
                        sh "bin/console assets:install"
                        sh "bin/console pim:installer:dump-require-paths"
                    }
                    container("node") {
                        sh "npm config set cache /shared/.npm --global"
                        // Required to avoid permission error
                        sh "npm config set unsafe-perm true"
                        sh "npm install"
                        sh "npm run webpack"
                    }
                    container("docker") {
                        sh "cp vendor/akeneo/pim-community-dev/Dockerfile ."
                        sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee ."
                        sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee"
                    }
                }
            }
        }
    }

    parallel tasks
}

stage("Test") {
    try {
        parallel(
            "phpunit": {
                withPhp({
                    sh "cd /home/jenkins/pim && vendor/bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test"
                })
            },
            "phpspec": {
                withPhp({
                    sh "cd /home/jenkins/pim && chown -R phpuser ."
                    sh "cd /home/jenkins/pim && su phpuser -c 'vendor/bin/phpspec run --format=dot'"
                })
            },
            "php-cs-fixer": {
                withPhp({
                    sh "cd /home/jenkins/pim && vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php"
                })
            },
            "grunt": {
                withNode({
                    sh "cd /home/jenkins/pim && npm run webpack"
                    sh "cd /home/jenkins/pim && npm run lint"
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
                            [container: "php", script: "php -d error_reporting='E_ALL' vendor/bin/phpunit -c app/phpunit.xml.dist " + file]
                        ])
                    }

                    return messages
                }, 20, "ce")
            },
            "behat-ce": {
                queue({
                    scenarios = sh (returnStdout: true, script: 'find /home/jenkins/pim/features -name "*.feature" -exec grep -En "(Scenario|Scenario Outline): " {} +').tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (scenario in scenarios) {
                        line = scenario.trim().substring(0, scenario.indexOf(":"))

                        messages.add([
                            [container: "php", script: "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"],
                            [container: "php", script: "cp behat.ci.yml behat.yml"],
                            [container: "php", script: "bin/console --env=behat --quiet pim:install --force"],
                            [container: "php", script: "chmod 777 -R var/cache/behat"],
                            [container: "php", script: "touch var/logs/behat.log"],
                            [container: "php", script: "chmod 777 -R var/logs/behat.log"],
                            [container: "php", script: "php vendor/bin/behat --tags=\"~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app\" --format progress --strict -vv " + line]
                        ])
                    }

                    return messages
                }, 300, "ce")
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
                            [container: "php", script: "php -d error_reporting='E_ALL' vendor/bin/phpunit -c app/phpunit.xml.dist " + file]
                        ])
                    }

                    return messages
                }, 20, "ee")
            },
            "behat-ee": {
                queue({
                    scenarios = sh (returnStdout: true, script: 'find /home/jenkins/pim/features /home/jenkins/pim/vendor/akeneo/pim-community-dev/features -name "*.feature" -exec grep -En "(Scenario|Scenario Outline): " {} +').tokenize('\n')
                    messages = new net.sf.json.JSONArray()

                    for (scenario in scenarios) {
                        line = scenario.trim().substring(0, scenario.indexOf(":"))

                        messages.add([
                            [container: "php", script: "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#database_host: .*#database_host: 127.0.0.1#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "sed -i \"s#index_hosts: .*#index_hosts: 'elastic:changeme@127.0.0.1:9200'#g\" app/config/parameters_test.yml"],
                            [container: "php", script: "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"],
                            [container: "php", script: "cp behat.ci.yml behat.yml"],
                            [container: "php", script: "bin/console --env=behat --quiet pim:install --force"],
                            [container: "php", script: "chmod 777 -R var/cache/behat"],
                            [container: "php", script: "touch var/logs/behat.log"],
                            [container: "php", script: "chmod 777 -R var/logs/behat.log"],
                            [container: "php", script: "php vendor/bin/behat --tags=\"~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce\" --format progress --strict -vv " + line]
                        ])
                    }

                    return messages
                }, 300, "ee")
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
                }
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
    podTemplate(label: "pubsub", containers: [
        containerTemplate(name: "gcloud", alwaysPullImage: true, ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/gcloud", envVars: [containerEnvVar(key: "PUBSUB_PROJECT_ID", value: "akeneo-ci")])
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-${edition}\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ], volumes: [
        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock"),
        hostPathVolume(hostPath: "/usr/bin/docker", mountPath: "/usr/bin/docker")
    ]) {
        node("pubsub") {
            container("gcloud") {
                sh "gcloud.phar pubsub:topic:create ${NODE_NAME}"
                sh "gcloud.phar pubsub:subscription:create ${NODE_NAME} ${NODE_NAME}-subscription"

                messages = body()

                writeJSON file: 'output.json', json: messages
                sh "gcloud.phar pubsub:message:publish ${NODE_NAME} output.json"

                sh "sed -i 's#JOB_NAME#${NODE_NAME}#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"
                sh "sed -i 's#SUBSCRIPTION_NAME#${NODE_NAME}-subscription#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"
                sh "sed -i 's#PIM_IMAGE#eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-${edition}#g' /home/jenkins/pim/.ci/k8s/pubsub_consumer_job.yaml"

                try {
                    sh "kubectl apply -f /home/jenkins/pim/.ci/k8s/"
                    sh "kubectl scale --replicas=${scale} jobs/${NODE_NAME}"
                    sh "gcloud.phar job:wait ${NODE_NAME}"
                } finally {
                    sh "kubectl delete job ${NODE_NAME}"
                    sh "gcloud.phar pubsub:topic:delete ${NODE_NAME}"
                    sh "gcloud.phar pubsub:subscription:delete ${NODE_NAME}-subscription"
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
