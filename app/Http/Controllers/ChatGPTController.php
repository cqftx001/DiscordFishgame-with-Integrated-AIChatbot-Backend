<?php

namespace App\Http\Controllers;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class ChatGPTController extends Controller
{

    public function chat(Request $request)
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY');

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $request->input('message')]
                ]
            ]
        ]);

        $body = $response->getBody();
        $result = json_decode($body, true);
        $filteredResult = $result['choices'][0]['message']['content'] ?? 'No content';

        return $this->success($filteredResult);
    }

    /**
     * @throws GuzzleException
     */
    public function command(Request $request)
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY');

        $message = $request->input('message');
        $keyWords = ['fish', 'play', 'sell'];

        $keyWordsStr = implode(',', $keyWords);

        $prompt = "Given the keywords [{$keyWordsStr}], please match them to the message: \"{$message}\" and return the matched keywords. And return the closest matched keyword. Cannot return none. Just return the keyword without any description!";

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are going to identify the keywords in a given sentences'],
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]
        ]);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $filteredResult = $result['choices'][0]['message']['content'] ?? 'No content';

        return $this->success($filteredResult);
    }

    private function execute($command, $user_id): \Illuminate\Http\JsonResponse
    {
        $client = new Client();
        $url = 'http://localhost:8081/api/execute';


        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'command' => $command,
                'user_id' => $user_id,
                'message' => $command
            ]
        ]);

        $body = $response->getBody();
        $result = json_decode($body, true);

        return response()->json(['success' => true, 'message' => "Executing command: " . $command, 'data' => $result]);
    }

    public function draw(Request $request)
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY');

        $prompt = $request->input('prompt');

        // 检查 prompt 是否存在
        if (!$prompt) {
            return $this->error('Prompt is required', 400);
        }

        // 根据样例构建详细的 prompt
        $detailedPrompt = " Given the keywords [{$prompt}], to draw an image relates to the keywords - requirements : Note: the img created should be more realistic, rather than in cartoon style.";

        try {
            $response = $client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'prompt' => $detailedPrompt,
                    'n' => 1,
                    'size' => '1024x1024'
                ]
            ]);

            $body = $response->getBody();
            $result = json_decode($body, true);

            return $this->success($result['data'][0]['url'] ?? 'No content');
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }



    public function error($message, $code = 500)
    {
        return response()->json(['error' => $message], $code);
    }

    public function success($data = [], $code = 200)
    {
        return response()->json(['data' => $data], $code);
    }

}
