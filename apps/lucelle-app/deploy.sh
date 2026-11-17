#!/bin/bash
set -e

PROJECT_DIR="$HOME/www/docker-stack/nextapps/lucelle-app"
START_TIME=$(date +%s)

echo "🔄 Déclenchement du workflow GitHub Actions..."
gh workflow run "Build Docker Image" --repo Graphandco/lucelle-app-supabase --ref main

echo "⏳ Attente de la fin du build..."
RUN_ID=$(gh run list --workflow="Build Docker Image" --repo Graphandco/lucelle-app-supabase --limit 1 --json databaseId,status -q '.[0].databaseId')

# Compteur en arrière-plan pendant gh run watch
(
  ELAPSED=0
  while true; do
    sleep 1
    ELAPSED=$((ELAPSED + 1))
    printf "\r   ⏱️  Build en cours... %d s   " "$ELAPSED"
  done
) &
TIMER_PID=$!

gh run watch "$RUN_ID" --repo Graphandco/lucelle-app-supabase --exit-status
kill $TIMER_PID 2>/dev/null
printf "\r"

echo "📥 Pull de la nouvelle image..."
cd "$PROJECT_DIR"
docker compose pull

echo "🚀 Redémarrage..."
docker compose up -d

END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))
MIN=$((DURATION / 60))
SEC=$((DURATION % 60))

echo "✅ Déploiement terminé."
echo "⏱️  Durée totale : ${MIN}m ${SEC}s"
