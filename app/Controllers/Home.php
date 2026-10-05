<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('chat_view');
    }

    public function send()
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $userMessage = $this->request->getPost('message');
        $apiKey = env('GROQ_API_KEY');

        if (empty($userMessage)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        }

        if (empty($apiKey)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'API Key Groq belum diatur di .env']);
        }

        try {
            $client = \Config\Services::curlrequest();

            // 1. Ambil daftar model aktif dari Groq
            $modelsResponse = $client->get('https://api.groq.com/openai/v1/models', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}"
                ],
                'timeout' => 10,
                'http_errors' => false
            ]);

            $activeModel = null;

            if ($modelsResponse->getStatusCode() === 200) {
                $modelsData = json_decode($modelsResponse->getBody(), true);
                if (!empty($modelsData['data'])) {
                    // Cari model Llama / Mixtral / Qwen standar yang ramah pengguna
                    foreach ($modelsData['data'] as $m) {
                        $modelId = $m['id'] ?? '';
                        
                        // Prioritaskan model keluarga llama atau mixtral publik
                        if (
                            preg_match('/^(llama|mixtral|qwen)/i', $modelId) && 
                            !preg_match('/(guard|whisper|vision|safeguard|arabic|canopylabs)/i', $modelId)
                        ) {
                            $activeModel = $modelId;
                            break;
                        }
                    }
                }
            }

            // Fallback ke model Llama utama jika filter di atas tidak menemukan
            if (!$activeModel) {
                $activeModel = 'llama-3.3-70b-versatile';
            }

            // 2. Kirim pesan ke API Groq
            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json'
                ],
                'timeout' => 20,
                'json' => [
                    'model' => $activeModel,
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'Kamu adalah Tel-U Bot, asisten virtual resmi Telkom University. Jawab pertanyaan dengan ramah, singkat, padat, dan jelas.'
                        ],
                        [
                            'role'    => 'user',
                            'content' => $userMessage
                        ]
                    ]
                ],
                'http_errors' => false
            ]);

            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200 && isset($body['choices'][0]['message']['content'])) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'reply'  => $body['choices'][0]['message']['content']
                ]);
            }

            $errMsg = $body['error']['message'] ?? 'HTTP Error ' . $response->getStatusCode();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Groq Error: {$errMsg}"
            ]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ]);
        }
    }
}