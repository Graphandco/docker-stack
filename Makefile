.PHONY: help up down restart logs ps pull infra-up infra-down apps-up apps-down

# ── Listes de services (chemins relatifs vers les dossiers contenant docker-compose.yml) ──

INFRA := infra/postgres infra/mysql infra/redis infra/adminer infra/minio \
         infra/supabase infra/watchtower infra/bitwarden infra/monitoring infra/dockerhub

APPS  := apps/nextcloud apps/convert-image apps/admin-dashboard/admin-api \
         apps/admin-dashboard/admin-panel apps/snapotter \
         directus wordpress matomo youclickyoucollect prestashop/trackinstudio

help: ## Afficher cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Service individuel ──────────────────────────────────

up: ## Démarrer un service (make up s=postgres)
	@docker compose -f $(call find_compose,$(s)) up -d

down: ## Stopper un service (make down s=postgres)
	@docker compose -f $(call find_compose,$(s)) down

restart: ## Redémarrer un service (make restart s=postgres)
	@docker compose -f $(call find_compose,$(s)) restart

logs: ## Logs d'un service (make logs s=postgres)
	@docker compose -f $(call find_compose,$(s)) logs -f --tail=100

pull: ## Pull les images d'un service (make pull s=postgres)
	@docker compose -f $(call find_compose,$(s)) pull

# ── Groupes ─────────────────────────────────────────────

infra-up: ## Démarrer toute l'infra
	@$(foreach svc,$(INFRA),docker compose -f $(svc)/docker-compose.yml up -d;)

infra-down: ## Stopper toute l'infra
	@$(foreach svc,$(INFRA),docker compose -f $(svc)/docker-compose.yml down;)

apps-up: ## Démarrer toutes les apps
	@$(foreach svc,$(APPS),docker compose -f $(svc)/docker-compose.yml up -d;)

apps-down: ## Stopper toutes les apps
	@$(foreach svc,$(APPS),docker compose -f $(svc)/docker-compose.yml down;)

all-up: infra-up apps-up ## Démarrer tout

all-down: apps-down infra-down ## Stopper tout

ps: ## État de tous les conteneurs
	@docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# ── Helpers ─────────────────────────────────────────────

define find_compose
$(shell \
	for dir in infra apps nextapps . ; do \
		f="$$dir/$(1)/docker-compose.yml"; \
		[ -f "$$f" ] && echo "$$f" && exit 0; \
	done; \
	f="$(1)/docker-compose.yml"; \
	[ -f "$$f" ] && echo "$$f")
endef
