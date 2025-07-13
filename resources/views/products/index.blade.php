<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Produk</title>
</head>
<body>
    <h1>Daftar Produk</h1>

    @if(session('success')) <p style="color: green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color: red">{{ session('error') }}</p> @endif

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Harga</th>
                <th>Visibilitas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td>{{ $product->is_visible ? 'Tampil' : 'Disembunyikan' }}</td>
                <td>
                    {{-- Tombol Sinkronisasi --}}
                    @if (!$product->hub_product_id)
                        <button type="button" onclick="syncToHub({{ $product->id }})">Sync ke Hub</button>
                    @else
                        {{-- Tombol Toggle --}}
                        <button onclick="toggleVisibility({{ $product->id }}, {{ $product->is_visible ? 'true' : 'false' }})">
                            {{ $product->is_visible ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>

                        {{-- Tombol Delete --}}
                        <button onclick="deleteFromHub({{ $product->id }})">Hapus dari Hub</button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <script>
        function syncToHub(productId) {
            console.log("syncToHub() dipanggil untuk ID:", productId); // DEBUG
            fetch(`{{ url('api/products') }}/${productId}/sync-to-hub`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => alert(data.message))
            .catch(err => alert("Gagal sinkronisasi: " + err));
        }

        function toggleVisibility(productId, isVisible) {
            fetch(`/api/products/${productId}/toggle-visibility`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_on: !isVisible })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(err => alert("Gagal toggle: " + err));
        }

        function deleteFromHub(productId) {
            if (!confirm('Yakin ingin hapus produk dari Hub?')) return;
            fetch(`/api/products/${productId}/delete-from-hub`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(err => alert("Gagal hapus dari Hub: " + err));
        }
    </script>
</body>
</html>
