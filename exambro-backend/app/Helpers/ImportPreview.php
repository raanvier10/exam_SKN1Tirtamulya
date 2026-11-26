<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImportPreview
{
    public static function storeAndRender(array $config, array $previewRows, array $readyPayload)
    {
        $token = Str::random(40);
        $cacheKey = self::cacheKey($token);

        Cache::put($cacheKey, $readyPayload, now()->addMinutes(30));

        $counts = [
            'all' => count($previewRows),
            'ready' => 0,
            'duplicate' => 0,
            'error' => 0,
        ];

        foreach ($previewRows as $row) {
            $st = $row['status'] ?? 'error';
            if (isset($counts[$st])) {
                $counts[$st]++;
            }
        }

        return view('admin.imports.preview', [
            'entityLabel' => $config['entityLabel'],
            'unitLabel' => $config['unitLabel'],
            'backRoute' => $config['backRoute'],
            'actionRoute' => $config['actionRoute'],
            'columns' => $config['columns'],
            'rows' => $previewRows,
            'counts' => $counts,
            'token' => $token,
            'fileName' => $config['fileName'] ?? 'file',
        ]);
    }

    public static function pullPayload(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        return Cache::pull(self::cacheKey($token));
    }

    protected static function cacheKey(string $token): string
    {
        return 'import_preview_' . Auth::id() . '_' . $token;
    }
}
