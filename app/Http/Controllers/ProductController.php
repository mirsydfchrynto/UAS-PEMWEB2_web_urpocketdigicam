<?php

namespace App\Http\Controllers;

use App\Models\Product; // Asumsi model produk Anda
use App\Services\HubApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Illuminate\Support\Facades\Log; // Untuk logging error

class ProductController extends Controller
{
    protected $hubApiService;


    public function __construct(HubApiService $hubApiService)
    {
        $this->hubApiService = $hubApiService;
    }
    /**
     * Mengatur visibilitas produk (On/Off) di Hub.
     */
    public function toggleVisibility(Request $request, Product $product)
    {
        $request->validate([
            'is_on' => 'required|boolean',
        ]);
        $isOn = $request->input('is_on');
        // Pastikan produk memiliki hub_product_id yang valid
        if (empty($product->hub_product_id)) {
            return response()->json(
                [
                    'message' => 'Product ID in Hub is missing. Please sync product first.'
                ],
                400
            );
        }
        try {
            DB::beginTransaction();
            if ($isOn) {
                // Jika On, update status visibilitas di Hub menjadi true
                $hubResponse = $this->hubApiService->updateProductVisibility(
                    $product->hub_product_id,
                    ['is_visible' => true]
                );
                // Opsional: update status lokal jika diperlukan
                $product->is_visible = true; // Asumsi ada kolom is_visible di tabel produk lokal
            } else {
                // Jika Off, update status visibilitas di Hub menjadi false
                $hubResponse = $this->hubApiService->updateProductVisibility(
                    $product->hub_product_id,
                    ['is_visible' => false]
                );
                // Opsional: update status lokal jika diperlukan
                $product->is_visible = false;
            }
            $product->save();
            DB::commit();
            return response()->json([
                'message' => 'Product visibility updated successfully.',
                'hub_response' => $hubResponse
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::rollBack();
            $statusCode = $e->getCode();
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            Log::error("API Hub Client Error: " . $e->getMessage() . " Response: " . json_encode($responseBody));
            return response()->json(
                [
                    'message' => 'Error from Hub API: ' . ($responseBody['message'] ?? 'Unknown error'),
                    'status' => $statusCode
                ],
                $statusCode
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to toggle product visibility: " . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to toggle product visibility. ' . $e->getMessage()
                ],
                500
            );
        }
    }
    /**
     * Metode untuk sinkronisasi awal atau membuat produk baru di Hub
     * Ini penting agar produk memiliki hub_product_id sebelum bisa di-toggle visibilitasnya.
     */
    public function syncProductToHub(Request $request, Product $product)
    {
        // Contoh data produk yang akan dikirim ke Hub. Sesuaikan dengan skema Hub.
        $productData = [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'category_id' => $product->category->hub_category_id ?? null, // Pastikan kategori juga disinkronkan dan punya ID Hub
            'is_visible' => $product->is_visible,
            // ... tambahkan data lain yang dibutuhkan Hub
        ];
        try {
            DB::beginTransaction();
            $hubResponse = $this->hubApiService->createProduct($productData);
            // Simpan ID produk dari Hub ke database lokal Anda
            $product->hub_product_id = $hubResponse['product_id']; // Sesuaikan dengan key response dari Hub
            $product->save();
            DB::commit();
            return response()->json([
                'message' => 'Product synced to Hub successfully.',
                'hub_product_id' => $product->hub_product_id,
                'hub_response' => $hubResponse
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::rollBack();
            $statusCode = $e->getCode();
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            Log::error("API Hub Client Error during sync: " . $e->getMessage() . " Response: " . json_encode($responseBody));
            return response()->json(
                [
                    'message' => 'Error from Hub API during sync: ' . ($responseBody['message'] ?? 'Unknown error'),
                    'status' => $statusCode
                ],
                $statusCode
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to sync product to Hub: " . $e->getMessage());
            return response()->json(['message' => 'Failed to sync product to Hub. ' . $e->getMessage()], 500);
        }
    }
    /**
     * Metode untuk menghapus produk dari Hub.
     */
    public function deleteProductFromHub(Request $request, Product $product)
    {
        if (empty($product->hub_product_id)) {
            return response()->json(['message' => 'Product ID in Hub is missing. Nothing to delete from Hub.'], 400);
        }
        try {
            DB::beginTransaction();
            $hubResponse = $this->hubApiService->deleteProduct($product->hub_product_id);
            // Opsional: Hapus hub_product_id dari lokal jika produk hanya disembunyikan
            // atau hapus produk lokal jika ini adalah full delete.
            $product->hub_product_id = null; // Contoh: Hapus referensi Hub ID
            $product->save();
            DB::commit();
            return response()->json([
                'message' => 'Product deleted from Hub successfully.',
                'hub_response' => $hubResponse
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::rollBack();
            $statusCode = $e->getCode();
            $responseBody = json_decode($e->getResponse()->getBody(), true);
            Log::error("API Hub Client Error during delete: " . $e->getMessage() . " Response: " . json_encode($responseBody));
            return response()->json(
                [
                    'message' => 'Error from Hub API during delete: ' . ($responseBody['message'] ?? 'Unknown error'),
                    'status' => $statusCode
                ],
                $statusCode
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete product from Hub: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete product from Hub. ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $products = Product::latest()->paginate(10); // Bisa juga ->get() kalau tidak ingin pagination
        return view('products.index', compact('products'));
    }

}
