<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elasticsearch Search - Laravel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 2rem;
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .search-box {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            background: var(--card-bg);
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0.75rem 1rem;
            color: var(--text-main);
            font-size: 1rem;
            outline: none;
        }

        .search-box button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-box button:hover {
            background: var(--primary-hover);
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .btn-seed {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-seed:hover {
            background: var(--primary);
            color: white;
        }

        .results {
            display: grid;
            gap: 1.5rem;
        }

        .result-item {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            transition: transform 0.2s, border-color 0.2s;
        }

        .result-item:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .result-item h3 {
            margin-top: 0;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .result-item p {
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .price {
            font-weight: 700;
            color: #10b981;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px dashed var(--border);
        }

        .pagination {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-main);
        }

        .pagination .active {
            background: var(--primary);
            border-color: var(--primary);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: #064e3b;
            color: #10b981;
            border: 1px solid #065f46;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>ElasticSearch Demo</h1>
            <p class="subtitle">Experience lightning fast search in Laravel</p>
        </header>

        @if(session('success'))
            <div class="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="actions">
            <form action="{{ route('products.seed') }}" method="POST">
                @csrf
                <button type="submit" class="btn-seed">Seed 50 Products</button>
            </form>
        </div>

        <form action="{{ route('products.index') }}" method="GET" class="search-box">
            <input type="text" name="query" placeholder="Search products (e.g. laptop, phone...)" value="{{ $query }}"
                autofocus>
            <button type="submit">Search</button>
        </form>

        <main class="results">
            @forelse($products as $product)
                <div class="result-item">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <span class="price">${{ number_format($product->price, 2) }}</span>
                </div>
            @empty
                <div class="no-results">
                    No products found. Use the seed button above to add some data or check if Elasticsearch is running.
                </div>
            @endforelse
        </main>

        <div class="pagination">
            {{ $products->links() }}
        </div>
    </div>
</body>

</html>