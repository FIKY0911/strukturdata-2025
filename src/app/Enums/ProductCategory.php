<?php

namespace App\Enums;

enum ProductCategory: string
{
    case KEMEJA = 'kemeja';
    case KAOS = 'kaos';

    public function label(): string
    {
        return match ($this) {
            self::KEMEJA => 'Kemeja',
            self::KAOS => 'Kaos',
        };
    }

    // public static function labels(): array
    // {
    //     return array_map(
    //         fn($case) => $case->label(),
    //         self::cases()
    //     );
    // }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

}
