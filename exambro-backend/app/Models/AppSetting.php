<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    private const PIN_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::find($key);
        return $row ? $row->value : $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function isWebEmergencyEnabled(): bool
    {
        return static::getValue('web_exam_enabled', '0') === '1';
    }

    public static function webEmergencyPin(): string
    {
        $pin = strtoupper(trim((string) static::getValue('web_exam_pin', '')));
        if ($pin === '') {
            return static::rotateWebEmergencyPin();
        }
        return $pin;
    }

    public static function rotateWebEmergencyPin(): string
    {
        $alphabet = self::PIN_ALPHABET;
        $max = strlen($alphabet) - 1;
        $pin = '';
        for ($i = 0; $i < 6; $i++) {
            $pin .= $alphabet[random_int(0, $max)];
        }
        static::setValue('web_exam_pin', $pin);
        return $pin;
    }
}
