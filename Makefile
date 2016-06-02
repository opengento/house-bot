.PHONY: dev-up

dev-up:
	docker-compose -f environments/development.yml build
	docker-compose -f environments/development.yml up

prod-up:
	docker-compose -f environments/production.yml build
	docker-compose -f environments/production.yml up -d

prod-down:
	docker-compose -f environments/production.yml down
