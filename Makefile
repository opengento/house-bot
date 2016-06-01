.PHONY: dev-up

dev-start:
	docker-compose -f environments/development.yml build
	docker-compose -f environments/development.yml up
