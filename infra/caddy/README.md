# Caddy — reverse proxy GraphandCo

Proxy HTTPS (auto TLS) pour tous les sites / services du stack Docker.

## Structure

```
infra/caddy/
├── Caddyfile          # options globales + imports
├── snippets.caddy     # blocs réutilisables
├── conf.d/            # un fichier par catégorie de sites
├── compose.yml
├── Dockerfile         # Caddy + plugin DNS Cloudflare
└── README.md
```

### Fichiers `conf.d/`

Alignés sur la hiérarchie `docker-stack/` (`sites` / `apps` / `infra`) :

| Fichier | Correspond à | Contenu |
|---------|--------------|---------|
| `10-sites-nextjs.caddy` | `sites/nextjs/` | graphandco, clients Next |
| `20-sites-wordpress.caddy` | `sites/wordpress/` | Multisite WP |
| `30-sites-prestashop.caddy` | `sites/prestashop/` | TrackInStudio |
| `40-sites-static.caddy` | (HTML / assets) | Statiques + assets.graphandco.net |
| `50-apps.caddy` | `apps/` | Matomo, YCYC, Snapotter, Nextcloud, admin-panel, sandbox… |
| `60-infra.caddy` | `infra/` | Adminer, Supabase, Bitwarden, registry, MinIO |

Le préfixe numérique fixe l’ordre de lecture. Le `Caddyfile` importe `conf.d/*.caddy` automatiquement.

## Snippets (`snippets.caddy`)

| Snippet | Usage |
|---------|--------|
| `proxy_app <host:port>` | logging + gzip/zstd + `reverse_proxy` |
| `www_to_apex <domaine>` | redirection 301 vers le domaine canonique |
| `secure_headers` | headers de sécurité (défaut) |
| `wp_multisite` | config WordPress (xmlrpc bloqué, cache static…) |
| `static_site` | `file_server` + cache + logging |
| `logging` | access log JSON (`/var/log/caddy/access.log`) |

### C’est quoi « apex » ?

L’**apex** (ou domaine apex / domaine nu) est le domaine **sans** `www` ni sous-domaine : `exemple.fr` plutôt que `www.exemple.fr`.

Le snippet `www_to_apex` redirige un alias (`www.…`, ancien domaine, etc.) vers ce domaine principal en **301** :

```caddy
www.rletudes.fr {
	import www_to_apex rletudes.fr
}
# → https://www.rletudes.fr/foo  →  https://rletudes.fr/foo
```

## Ajouter un site

### App Docker simple (Next, API, etc.)

Dans le bon fichier `conf.d/` selon le dossier du projet :
- Next client → `10-sites-nextjs.caddy`
- App interne → `50-apps.caddy`
- Service infra → `60-infra.caddy`

```caddy
mon-site.fr {
	import proxy_app mon-service:3000
}
www.mon-site.fr {
	import www_to_apex mon-site.fr
}
```
### Site WordPress

Ajouter le domaine dans le bloc `import wp_multisite` de `20-sites-wordpress.caddy`, plus la redirection www si besoin.

### Site statique

```caddy
demo.graphandco.com {
	root * /var/www/html/small-projects/demo
	import static_site
}
www.demo.graphandco.com {
	import www_to_apex demo.graphandco.com
}
```

### Cas particuliers

Ne pas forcer `proxy_app` si tu as besoin de :
- timeouts / `request_body` (Snapotter, convert-image)
- `basic_auth`, TLS DNS, `php_fastcgi`
- headers / CSP custom (PrestaShop, Nextcloud)

Garder alors `import logging` + `encode gzip zstd` + la config spécifique.

## Secrets (`.env`)

```bash
cp .env.example .env
# renseigner CLOUDFLARE_API_TOKEN (permission Zone:DNS:Edit)
```

Le fichier `.env` est gitignored (`**/.env`). Après modification : `docker compose up -d` (pas seulement reload).

## Valider et recharger

Depuis `infra/caddy/` :

```bash
docker compose exec caddy caddy validate --config /etc/caddy/Caddyfile
docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile --address localhost:2019
```

Toujours **validate** avant **reload**. Ne pas exposer le port admin `2019` sur l’hôte.

## Volumes utiles

- Config : `./Caddyfile`, `./conf.d`, `./snippets.caddy` (montés en lecture)
- Certificats / état : volumes Docker `caddy_data`, `caddy_config`
- Logs : `/var/log/caddy` sur l’hôte

## Éditeur (Cursor / VS Code)

L’extension **Caddyfile Support** (matthewpi) ne colorise par défaut que les fichiers nommés `Caddyfile`.  
Ce repo associe aussi `*.caddy` via `.vscode/settings.json`. Si ce n’est pas pris en compte : `Ctrl+K M` → choisir **Caddyfile**, ou recharger la fenêtre.
