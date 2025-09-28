# Makefile

# Laravel Base イメージをビルド
laravel-base:
	docker build -f docker/base.Dockerfile -t laravel-base .

# 全体をビルドして起動
build-up: laravel-base
	docker-compose build && docker-compose up -d

# 起動
up:
	docker-compose up -d

# 全体を停止
down:
	docker-compose down

# クリーンアップ（ビルドキャッシュ含む）
clean:
	docker system prune -af --volumes

laravel-app:
	docker-compose exec laravel-app bash

mysql-root:
	docker exec -it mysql mysql -uroot -proot

mysql-laravel:
	docker exec -it mysql mysql -u laravel -psecret laravel

mysql-replica-root:
	docker exec -it mysql-replica mysql -uroot -proot

mysql-replica-laravel:
	docker exec -it mysql-replica mysql -u laravel -psecret laravel

# DDLファイル出力
dump-ddl:
	docker exec -i mysql mysqldump -u laravel -psecret --no-data --no-tablespaces laravel > database/schema/schema.sql

# マイグレーション実行（docker外から）
migrate:
	docker-compose exec laravel-app php artisan migrate

# マイグレーションのリフレッシュ（リセット＋再実行）
migrate-seed:
	docker-compose exec laravel-app php artisan migrate:fresh --seed
