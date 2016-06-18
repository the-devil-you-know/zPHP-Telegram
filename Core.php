<?
declare(strict_types = 1);

namespace zPHP\Telegram;

class Core {

	const apiUrl = 'https://api.telegram.org/bot';


	static function rendForWebHook (string $method, array $pars) {
		$pars['method'] = $method;
		header('Content-Type: application/json');
		echo json_encode($pars);
	}

	static function setWebHook (string $token, string $url) {
		return self::request($token, 'setWebhook', ['url' => $url]);
	}

	static function sendMessage (string $token, int $chatId, string $text) {
		return self::request($token, 'sendMessage', [
			'chat_id' => $chatId,
			'text'    => $text
		]);
	}


	static private function request (string $token, string $method, array $data) {
		foreach ($data as $key => &$val) {
			// encoding to JSON array parameters, for example reply_markup
			if (!is_numeric($val) && !is_string($val))
				$val = json_encode($val);
		}
		$url = self::apiUrl . $token . '/' . $method . '?' . http_build_query($data);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);

		$response = curl_exec($curl);

		if ($response === FALSE) {
			curl_close($curl);
			return NULL;
		}

		$httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($httpCode >= 500)
			return NULL;
		if ($httpCode == 401)
			return NULL;

		$response = json_decode($response, TRUE);
		if ($httpCode != 200)
			return NULL;

		return $response['result'];
	}
}