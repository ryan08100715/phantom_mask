# Response

## Requirement Completion Rate

* [x] List pharmacies, optionally filtered by specific time and/or day of the week.
    * Implemented at `/api/pharmacies` API. (文檔對應於 `Pharmacy` 分類的 `獲取藥局清單`)
* [x] List all masks sold by a given pharmacy with an option to sort by name or price.
    * Implemented at `/api/pharmacies/{pharmacy}/masks` API. (文檔對應於 `Pharmacy` 分類的 `獲取某間藥局的口罩販售清單`)
* [x] List all pharmacies that offer a number of mask products within a given price range, where the count is above,
  below, or between given thresholds.
    * Implemented at `/api/pharmacies/search` API. (文檔對應於 `Pharmacy` 分類的 `搜尋藥局`)
* [x] Show the top N users who spent the most on masks during a specific date range.
    * Implemented at `/api/users/top-spenders` API. (文檔對應於 `User` 分類的 `取得高消費使用者`)
* [x] Process a purchase where a user buys masks from multiple pharmacies at once.
    * Implemented at `/api/users/{user}/purchases` API. (文檔對應於 `User` 分類的 `使用者購買`)
* [x] Update the stock quantity of an existing mask product by increasing or decreasing it.
    * Implemented at `/api/masks/{mask}` API. (文檔對應於 `Mask` 分類的 `更新口罩庫存數量`)
* [x] Create or update multiple mask products for a pharmacy at once, including name, price, and stock quantity.
    * Implemented at `/api/pharmacies/{pharmacy}/masks/batch` API. (文檔對應於 `Pharmacy` 分類的
      `批量新增或更新藥局口罩`)
* [x] Search for pharmacies or masks by name and rank the results by relevance to the search term.

  由於不確定需求是要同時搜尋 `pharmacies` 跟 `masks`，還是分開搜尋，所以我以分開搜尋實作
    * Implemented at `/api/pharmacies/search` API. (文檔對應於 `Pharmacy` 分類的
      `搜尋藥局`)
    * Implemented at `/api/masks/search` API. (文檔對應於 `Mask` 分類的
      `搜尋口罩`)

## API Document

有三種 API 文件：

1. 啟動服務後，瀏覽 `/docs` 路徑 (例如: `http://localhost:8000/docs`)
2. [postman collection](docs/docs.postman)：可將該檔案 import 進 postman 使用
3. [openapi](docs/docs.openapi.yaml)：可以使用 openapi 閱讀器觀看。(例如：[swagger editor](https://editor.swagger.io/))

> 如有問題以 `/docs` 文檔為主

## Import Data Commands

> 導入資料之前，請先配置好 `.env` 檔案跟執行 database migrate，詳情請看 Deployment

```bash
$ php artisan import:pharmacies [PATH_TO_FILE]
$ php artisan import:users [PATH_TO_FILE]
```

例如導入專案內的 json 檔案，在專案根目錄執行：

```bash
$ php artisan import:pharmacies ./storage/data/pharmacies.json
$ php artisan import:users ./storage/data/users.json
```

## Test Coverage Report

我為我建立的 API 編寫了 50 個測試(Unit + Feature)。

* 請[點擊此處](storage/coverage/test-coverage.jpg)查看測試覆蓋率圖檔。
* 或打開 `storage/coverage/index.html` 查看測試覆蓋率報告。

您可以使用以下命令執行測試：

```bash
php artisan test
```

## Deployment

### 環境建置 (以 ubuntu24.04 為例)

```bash
# 更新系統
$ sudo apt-get update
$ sudo apt-get upgrade -y

# 安裝常用工具
$ sudo apt-get install -y zip unzip curl

# 匯入 PHP PPA
$ sudo apt-get install -y software-properties-common
$ sudo add-apt-repository -y ppa:ondrej/php
$ sudo apt-get update

# 安裝 PHP
$ sudo apt-get install -y php8.3 php8.3-fpm

# 安裝 PHP Extensions
$ sudo apt-get install -y php8.3-curl php8.3-mbstring php8.3-xml php8.3-intl
$ sudo apt-get install -y php8.3-sqlite3 php8.3-mysql
$ sudo apt-get install -y php8.3-bcmath php8.3-zip

# 安裝 Composer
$ curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
$ echo 'export PATH="$HOME/.config/composer/vendor/bin:$PATH"' >>~/.bashrc
```

### 專案部屬

```bash
$ git clone https://github.com/ryan08100715/phantom_mask.git
```

#### Env 配置

1. 將 `.env.example` 複製成 `.env` 並配置
2. 產生 laravel 專案 key
    ```bash
    $ php artisan key:generate
    ```
3. 設置資料庫相關環境變數，根據需求自行設置，資料庫使用 mysql
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=phantom_mask
    DB_USERNAME=root
    DB_PASSWORD=
    ```

#### 安裝依賴

```bash
$ composer install
```

#### 資料庫設置

```bash
$ php artisan migrate
```

#### 導入資料

```bash
$ php artisan import:pharmacies ./storage/data/pharmacies.json
$ php artisan import:users ./storage/data/users.json
```

#### 啟動服務

> 預設會啟動在 port 8000

```bash
$ php artisan serve
```

## Additional Data

* 資料庫設計圖 [drawdb](docs/phantom_mask_drawdb_2025-06-13T13_19_29.650Z.json)
  ：可以透過 [drawdb editor](https://www.drawdb.app/editor) 導入觀看。
