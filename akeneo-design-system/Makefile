
DOCKER_PORT_STORYBOOK = 6006
_YARN = docker run -u node -v `pwd`:/srv/ads -w /srv/ads -p $(DOCKER_PORT_STORYBOOK):80 --rm -it node:13 yarn

# Install dependencies
_yarn:
	$(_YARN)

# Start Storybook development server
.PHONY: storybook
storybook: _yarn
	@echo "\e[7m\e[92m Start Storybook development server on http://localhost:$(DOCKER_PORT_STORYBOOK)/ \e[0m"
	$(_YARN) run start-storybook -h 0.0.0.0 -p 80 --ci
