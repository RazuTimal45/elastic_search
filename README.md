# Laravel + Elasticsearch Demo

This project demonstrates how to implement a full-stack search experience using Laravel and Elasticsearch. For educational purposes, it uses a **custom Scout Engine** to showcase exactly how the integration works.

## Prerequisites

- PHP 8.2+
- MySQL 
- Elasticsearch 8.x

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
```

### 2. Configure Environment
Update your `.env` file with your MySQL and Elasticsearch credentials:
```env
DB_CONNECTION=mysql
DB_DATABASE=elastic_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=http://localhost:9200
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Running Elasticsearch

#### Option A: Docker (Recommended)
If you have Docker, run:
```bash
docker run -d --name elasticsearch -p 9200:9200 -e "discovery.type=single-node" -e "xpack.security.enabled=false" elasticsearch:8.12.0
```

#### Option B: Manual Installation (Ubuntu)
```bash
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elastic-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/elastic-keyring.gpg] https://artifacts.elastic.co/packages/8.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-8.x.list
sudo apt update && sudo apt install elasticsearch
sudo systemctl enable --now elasticsearch
```

### 5. Indexing Data
After seeding data using the UI button at `/products`, you can import it into Elasticsearch:
```bash
php artisan scout:import "App\Models\Product"
```

## How it Works

1.  **Searchable Trait**: The `Product` model uses `Laravel\Scout\Searchable`.
2.  **Custom Engine**: `App\Search\Engines\ElasticsearchEngine.php` handles the low-level communication with Elasticsearch using the official `elasticsearch/elasticsearch` PHP client.
3.  **AppServiceProvider**: The custom engine is registered in `AppServiceProvider::boot()`.
4.  **Premium UI**: The results are displayed in a modern, dark-themed UI at `/products`.

## Exploring the Code

- **Engine Logic**: `app/Search/Engines/ElasticsearchEngine.php`
- **Mapping Data**: See `toSearchableArray()` in `app/Models/Product.php`.
- **Search Query**: See `ProductController::index()` where `Product::search($query)` is called.
