<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class HubApiService
{
    protected $client;
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->baseUrl = env('HUB_API_URL');
        $this->clientId = env('HUB_CLIENT_ID');
        $this->clientSecret = env('HUB_CLIENT_SECRET');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                // Anda mungkin perlu menambahkan header otorisasi di sini setelah mendapatkan token
            ],
            'verify' => false, // Set false jika menggunakan localhost atau self-signed certs, HANYA untukdevelopment!
        ]);
    }

    // * Mendapatkan token akses dari Hub menggunakan Client Credentials Grant.
// * Token ini akan digunakan untuk otentikasi API ke Hub.

    public function getAccessToken()
    {
        try {
            $response = Http::asForm()->post(config('services.hub.url') . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.hub.client_id'),
                'client_secret' => config('services.hub.client_secret'),
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('Gagal mendapatkan access token dari Hub', [
                'response' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception saat get access token: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Mengirim permintaan PUT/PATCH ke Hub untuk mengupdate status produk.
     *
     * @param int $productId ID produk di Hub
     * @param array $data Data yang akan diupdate (misal: ['is_visible' => true/false])
     * @return mixed Respon dari Hub
     */
    public function updateProductVisibility($productId, array $data)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json(['message' => 'Failed to get access token from Hub'], 500);
        }
        try {
            $response = $this->client->put("products/{$productId}/visibility", [ // Sesuaikan endpoint Hub
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => $data,
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Log error dan lempar exception agar bisa ditangkap di controller
            Log::error('Client Error saat update visibilitas produk di Hub: ' . $e->getMessage() . ' Response: ' .
                $e->getResponse()->getBody());
            throw $e; // Lempar exception agar bisa ditangkap di controller
        } catch (\Exception $e) {
            Log::error('Error saat update visibilitas produk di Hub: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengirim permintaan POST ke Hub untuk membuat produk baru.
     * @param array $data Data produk baru
     * @return mixed Respon dari Hub
     */
    public function createProduct($data)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            // Jangan return response langsung
            throw new \Exception("Failed to get access token");
        }

        try {
            $response = $this->client->post("products", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode((string) $response->getBody(), true); // ✅ BALIKIN ARRAY
        } catch (\Exception $e) {
            Log::error("Create product error: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Mengirim permintaan DELETE ke Hub untuk menghapus produk.
     * @param int $productId ID produk di Hub
     * @return mixed Respon dari Hub
     */
    public function deleteProduct($productId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json(['message' => 'Failed to get access token from Hub'], 500);
        }
        try {
            $response = $this->client->delete("products/{$productId}", [ // Sesuaikan endpoint Hub
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Client Error saat menghapus produk di Hub: ' . $e->getMessage() . ' Response: ' .
                $e->getResponse()->getBody());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error saat menghapus produk di Hub: ' . $e->getMessage());
            throw $e;
        }
    }
}
