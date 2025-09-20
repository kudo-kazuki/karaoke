# 🎵 Music Admin System

## 🚀 初回セットアップ (ローカル開発環境)

1. **Docker ビルド & 起動**

    ```bash
    docker-compose build
    docker-compose up -d
    ```

2. **PHP コンテナに入る**

    ```bash
    docker-compose exec php bash
    cd api
    ```

3. **DB マイグレーション実行 (Phinx)**

    ```bash
    php ../vendor/bin/phinx migrate
    ```

    → phpMyAdmin (http://localhost:8083) でテーブルが作成されているか確認できます。

4. **フロントエンド依存関係のインストール**

    ```bash
    npm ci
    ```

5. **開発サーバー起動**
    ```bash
    npm run dev
    ```
    → http://localhost:5174 にアクセス

---

## 📦 本番デプロイ手順

1. **フロントエンドをビルド**

    ```bash
    npm run build
    ```

    → `dist/` フォルダが生成されます。

2. **環境変数ファイルを用意**
   `api/.env.production` を作成し、本番用の DB 情報と JWT_SECRET を記入してください。

    ```env
    DB_HOST=xxxx
    DB_DATABASE=xxxx
    DB_USERNAME=xxxx
    DB_PASSWORD=xxxx
    JWT_SECRET=xxxx
    APP_ENV=production
    APP_URL=https://xxxxx.com
    ```

3. **アップロードするファイル**
   以下をサーバーに配置します:
    - `dist/` の中身（フロントエンドのビルド成果物）
    - `vendor/`（Composer 依存関係）
    - `api/`（PHP API 一式）
    - `.htaccess`（ルーティング用）

4. **初回はマイグレーション**

    ```bash
    cd api
    php ../vendor/bin/phinx migrate -e production
    ```

5. **アクセス確認**
    - フロント: `https://your-domain/`
    - API: `https://your-domain/api/...`

---

## 📝 備考

- **JWT_SECRET** は本番とローカルで異なる値を必ず設定してください。
- **.env.production** は Git 管理に含めないよう注意。
