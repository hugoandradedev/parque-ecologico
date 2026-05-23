<?php

function rateLimitKey(string $scope): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return hash('sha256', $scope . '|' . $ip);
}

function checkRateLimit(string $scope, int $maxAttempts, int $windowSeconds): bool {
    $dir = sys_get_temp_dir() . '/parque_rate_limits';

    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    $file = $dir . '/' . rateLimitKey($scope) . '.json';
    $now = time();
    $state = ['reset_at' => $now + $windowSeconds, 'attempts' => 0];

    $handle = fopen($file, 'c+');
    if ($handle === false) {
        return true;
    }

    try {
        flock($handle, LOCK_EX);
        $contents = stream_get_contents($handle);
        if ($contents !== false && $contents !== '') {
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $state = array_merge($state, $decoded);
            }
        }

        if (($state['reset_at'] ?? 0) <= $now) {
            $state = ['reset_at' => $now + $windowSeconds, 'attempts' => 0];
        }

        $state['attempts']++;

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state));

        return $state['attempts'] <= $maxAttempts;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

?>
