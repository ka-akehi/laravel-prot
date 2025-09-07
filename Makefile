# Makefile

# Laravel Base イメージをビルド
laravel-base:
	docker build -f docker/base.Dockerfile -t laravel-base .

# 全体をビルドして起動
up: laravel-base
	docker-compose build && docker-compose up -d

# 全体を停止
down:
	docker-compose down

# クリーンアップ（ビルドキャッシュ含む）
clean:
	docker system prune -af --volumes
