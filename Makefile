IMAGE = gammadia-date-time-extra-php

DOCKER_RUN = docker container run -it --rm -v ${PWD}:/app/ $(IMAGE)

.PHONY: up
up:
	docker buildx build . -t $(IMAGE)
	$(DOCKER_RUN) composer install
	$(DOCKER_RUN) sh
